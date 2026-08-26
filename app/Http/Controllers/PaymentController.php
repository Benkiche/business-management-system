<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Payment;
use App\Models\Customer;
use App\Services\PaymentService;
use App\Services\AuditService;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->paymentService = $paymentService;
    }

    /**
     * Show payment form for a sale.
     */
    public function create(Sale $sale): View
    {
        if ($sale->isCancelled()) {
            abort(403, 'Cannot pay cancelled sale');
        }

        if ($sale->isPaid()) {
            abort(403, 'Sale already fully paid');
        }

        AuditService::log(
            'viewed',
            'Payment',
            null,
            "Opened payment form for {$sale->invoice_number}"
        );

        return view('payments.create', compact('sale'));
    }

    /**
     * Store payment for sale.
     */
    public function store(Request $request, Sale $sale): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01|max:' . $sale->outstanding_balance,
                'payment_method' => 'required|in:cash,credit_card,check,bank_transfer',
                'payment_date' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string|max:500',
            ]);

            $amount = $validated['amount'];
            $oldStatus = $sale->payment_status;

            $this->paymentService->recordPayment($sale, $validated);
            $sale->refresh();

            AuditService::log(
                'created',
                'Payment',
                null,
                "Recorded \${$amount} payment for {$sale->invoice_number}",
                ['status' => $oldStatus],
                ['status' => $sale->payment_status]
            );

            AlertService::notifyPaymentReceived($sale, $amount);

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', "Payment of \${$amount} recorded successfully");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Record general customer payment.
     */
    public function storeGeneral(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,credit_card,check,bank_transfer',
                'payment_date' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string',
            ]);

            $customer = Customer::findOrFail($validated['customer_id']);
            $oldBalance = $customer->outstanding_balance;

            $this->paymentService->recordGeneralPayment($customer, $validated);
            $customer->refresh();

            AuditService::log(
                'created',
                'Payment',
                null,
                "General payment of \${$validated['amount']} from {$customer->name}",
                ['balance' => $oldBalance],
                ['balance' => $customer->outstanding_balance]
            );

            return back()
                ->with('success', "Payment applied to {$customer->name}'s account");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show payment history for customer.
     */
    public function history(Customer $customer): View
    {
        $payments = $customer->payments()
            ->with('recordedBy', 'sale')
            ->latest()
            ->paginate(20);

        AuditService::log(
            'viewed',
            'Payment',
            null,
            "Viewed payment history for {$customer->name}"
        );

        return view('payments.history', compact('customer', 'payments'));
    }

    /**
     * Show payment details.
     */
    public function show(Payment $payment): View
    {
        return view('payments.show', compact('payment'));
    }

    /**
     * List all payments.
     */
    public function index(Request $request): View
    {
        $query = Payment::with(['customer', 'recordedBy', 'sale'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->paginate(20);

        return view('payments.index', compact('payments'));
    }

    /**
     * Export payments to CSV.
     */
    public function export(Request $request)
    {
        $payments = Payment::with(['customer', 'recordedBy'])
            ->latest()
            ->get();

        if ($request->filled('from_date')) {
            $payments = $payments->where('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $payments = $payments->where('payment_date', '<=', $request->to_date);
        }

        $csv = "PAYMENT EXPORT\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $csv .= "Reference,Date,Customer,Amount,Method,Recorded By\n";

        foreach ($payments as $payment) {
            $csv .= "\"{$payment->payment_reference}\",";
            $csv .= "\"{$payment->payment_date->format('M d, Y')}\",";
            $csv .= "\"{$payment->customer->name}\",";
            $csv .= "\$" . number_format($payment->amount, 2) . ",";
            $csv .= $payment->payment_method . ",";
            $csv .= $payment->recordedBy->name . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payments-' . date('Y-m-d') . '.csv"',
        ]);
    }
}