<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Services\SaleService;
use App\Services\PaymentService;
use App\Services\AuditService;
use App\Services\AlertService;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SaleController extends Controller
{
    protected SaleService $saleService;
    protected PaymentService $paymentService;

    public function __construct(SaleService $saleService, PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->saleService = $saleService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display all sales.
     */
    public function index(Request $request): View
    {
        $query = Sale::with(['customer', 'salesperson'])->latest('sale_date');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->paginate(20);

        // Audit log
        AuditService::log(
            'viewed',
            'Sale',
            null,
            'Viewed sales list'
        );

        return view('sales.index', compact('sales'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')
            ->where('quantity_on_hand', '>', 0)
            ->orderBy('name')
            ->get();

        return view('sales.create', compact('customers', 'products'));
    }

    /**
     * Store a sale.
     */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['salesperson_id'] = auth()->id();

            $sale = $this->saleService->createSale($data);

            // Audit log
            AuditService::log(
                'created',
                'Sale',
                $sale->id,
                "Created invoice {$sale->invoice_number}",
                null,
                [
                    'invoice_number' => $sale->invoice_number,
                    'customer_id' => $sale->customer_id,
                    'amount' => $sale->grand_total,
                ]
            );

            // Notify managers
            AlertService::notifySaleCreated($sale);

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', "Sale created successfully! Invoice: {$sale->invoice_number}");
        } catch (\Exception $e) {
            AuditService::log(
                'created',
                'Sale',
                null,
                'Failed to create sale',
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()
                ->withInput()
                ->with('error', 'Failed to create sale: ' . $e->getMessage());
        }
    }

    /**
     * Display a sale.
     */
    public function show(Sale $sale): View
    {
        $sale->load('customer', 'salesperson', 'items.product', 'payments.recordedBy');

        AuditService::log(
            'viewed',
            'Sale',
            $sale->id,
            "Viewed invoice {$sale->invoice_number}"
        );

        return view('sales.show', compact('sale'));
    }

    /**
     * Edit sale (if allowed).
     */
    public function edit(Sale $sale): View
    {
        if ($sale->isCancelled()) {
            abort(403, 'Cannot edit cancelled sale');
        }

        $customers = Customer::active()->get();
        $products = Product::active()->get();

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    /**
     * Update sale.
     */
    public function update(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->isCancelled()) {
            return back()->with('error', 'Cannot edit cancelled sale');
        }

        if ($sale->amount_paid > 0) {
            return back()->with('error', 'Cannot edit sale with payments');
        }

        try {
            // Validate
            $request->validate([
                'notes' => 'nullable|string|max:1000',
                'due_date' => 'nullable|date',
            ]);

            $sale->update($request->only(['notes', 'due_date']));

            AuditService::log(
                'updated',
                'Sale',
                $sale->id,
                "Updated invoice {$sale->invoice_number}"
            );

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a sale.
     */
    public function cancel(Sale $sale): RedirectResponse
    {
        try {
            $invoiceNumber = $sale->invoice_number;
            $this->saleService->cancelSale($sale);

            AuditService::log(
                'cancelled',
                'Sale',
                $sale->id,
                "Cancelled invoice {$invoiceNumber}"
            );

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale cancelled and inventory restored');
        } catch (\Exception $e) {
            AuditService::log(
                'cancelled',
                'Sale',
                $sale->id,
                'Failed to cancel sale',
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record payment.
     */
    public function recordPayment(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->isCancelled()) {
            return back()->with('error', 'Cannot pay cancelled sale');
        }

        if ($sale->isPaid()) {
            return back()->with('error', 'Sale already paid');
        }

        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01|max:' . $sale->outstanding_balance,
                'payment_method' => 'required|in:cash,credit_card,check,bank_transfer',
                'payment_date' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string|max:500',
            ]);

            $amount = $request->amount;

            $this->paymentService->recordPayment($sale, $request->all());
            $sale->refresh();

            AuditService::log(
                'created',
                'Payment',
                null,
                "Payment of \${$amount} received for {$sale->invoice_number}"
            );

            AlertService::notifyPaymentReceived($sale, $amount);

            return back()->with('success', 'Payment recorded successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * View sales report.
     */
    public function report(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $sales = Sale::completed()
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->with('customer', 'salesperson', 'items.product')
            ->get();

        $totalRevenue = $sales->sum('grand_total');
        $totalPaid = $sales->sum('amount_paid');
        $totalOutstanding = $sales->sum(function ($sale) {
            return $sale->outstanding_balance;
        });

        $totalCost = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $totalCost += $item->quantity * $item->product->purchase_price;
            }
        }

        $report = [
            'total_sales' => $sales->count(),
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $totalRevenue - $totalCost,
            'profit_margin' => $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0,
            'total_paid' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'average_sale' => $sales->count() > 0 ? $totalRevenue / $sales->count() : 0,
        ];

        AuditService::log(
            'viewed',
            'Sale',
            null,
            "Generated sales report from {$fromDate} to {$toDate}"
        );

        return view('sales.report', compact('report', 'fromDate', 'toDate', 'sales'));
    }

    /**
     * Export sales to CSV.
     */
    public function export(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $sales = Sale::completed()
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->with('customer', 'salesperson')
            ->get();

        $csv = "SALES EXPORT\n";
        $csv .= "Period: {$fromDate} to {$toDate}\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $csv .= "Invoice,Date,Customer,Salesperson,Amount,Paid,Outstanding,Status\n";

        foreach ($sales as $sale) {
            $csv .= "\"{$sale->invoice_number}\",";
            $csv .= "\"{$sale->sale_date->format('M d, Y')}\",";
            $csv .= "\"{$sale->customer->name}\",";
            $csv .= "\"{$sale->salesperson->name}\",";
            $csv .= "\$" . number_format($sale->grand_total, 2) . ",";
            $csv .= "\$" . number_format($sale->amount_paid, 2) . ",";
            $csv .= "\$" . number_format($sale->outstanding_balance, 2) . ",";
            $csv .= $sale->payment_status . "\n";
        }

        AuditService::log(
            'exported',
            'Sale',
            null,
            "Exported {$sales->count()} sales records"
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Get sale item list (AJAX).
     */
    public function getItems(Sale $sale)
    {
        return response()->json($sale->items->load('product')->map(function ($item) {
            return [
                'id' => $item->id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount_percent . '%',
                'total' => '$' . number_format($item->line_total, 2),
            ];
        }));
    }

    /**
     * Delete sale (admin only).
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($sale->amount_paid > 0) {
            return back()->with('error', 'Cannot delete sale with payments');
        }

        try {
            $invoiceNumber = $sale->invoice_number;
            $sale->delete();

            AuditService::log(
                'deleted',
                'Sale',
                $sale->id,
                "Deleted invoice {$invoiceNumber}"
            );

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}