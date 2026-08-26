<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockOutRequest;
use App\Http\Requests\StockAdjustmentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    /**
     * Constructor.
     */
    public function __construct(InventoryService $inventoryService)
    {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory dashboard.
     */
    public function index(): View
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();
        $outOfStockProducts = $this->inventoryService->getOutOfStockProducts();
        $valuation = $this->inventoryService->getInventoryValuationReport();

        return view('inventory.index', compact(
            'lowStockProducts',
            'outOfStockProducts',
            'valuation'
        ));
    }

    /**
     * Show stock in form.
     */
    public function showStockInForm(): View
    {
        $products = Product::active()->with('supplier')->get();

        return view('inventory.stock-in', compact('products'));
    }

    /**
     * Process stock in.
     */
    public function stockIn(StockInRequest $request): RedirectResponse
    {
        try {
            $product = Product::findOrFail($request->product_id);

            $this->inventoryService->stockIn(
                product: $product,
                quantity: $request->quantity,
                unitCost: $request->unit_cost ?? $product->purchase_price,
                notes: $request->notes,
                userId: auth()->id()
            );

            return redirect()
                ->route('inventory.index')
                ->with('success', "Successfully recorded stock in of {$request->quantity} units for {$product->name}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record stock in: ' . $e->getMessage());
        }
    }

    /**
     * Show stock out form.
     */
    public function showStockOutForm(): View
    {
        $products = Product::active()
            ->where('quantity_on_hand', '>', 0)
            ->get();

        return view('inventory.stock-out', compact('products'));
    }

    /**
     * Process stock out.
     */
    public function stockOut(StockOutRequest $request): RedirectResponse
    {
        try {
            $product = Product::findOrFail($request->product_id);

            $this->inventoryService->stockOut(
                product: $product,
                quantity: $request->quantity,
                reason: $request->reason,
                notes: $request->notes,
                userId: auth()->id()
            );

            return redirect()
                ->route('inventory.index')
                ->with('success', "Successfully recorded stock out of {$request->quantity} units for {$product->name}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record stock out: ' . $e->getMessage());
        }
    }

    /**
     * Show adjustment form.
     */
    public function showAdjustmentForm(): View
    {
        $products = Product::active()->get();

        return view('inventory.adjustment', compact('products'));
    }

    /**
     * Process adjustment.
     */
    public function adjust(StockAdjustmentRequest $request): RedirectResponse
    {
        try {
            $product = Product::findOrFail($request->product_id);

            $this->inventoryService->adjustInventory(
                product: $product,
                newQuantity: $request->new_quantity,
                reason: $request->reason,
                notes: $request->notes,
                userId: auth()->id()
            );

            return redirect()
                ->route('inventory.index')
                ->with('success', "Successfully adjusted inventory for {$product->name}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to adjust inventory: ' . $e->getMessage());
        }
    }

    /**
     * Show movement history for a product.
     */
    public function productHistory(Product $product): View
    {
        $movements = $product->inventoryMovements()
            ->with('creator')
            ->paginate(25);

        $summary = $this->inventoryService->getInventorySummary($product);

        return view('inventory.product-history', compact('product', 'movements', 'summary'));
    }

    /**
     * Show inventory valuation report.
     */
    public function valuationReport(): View
    {
        $valuation = $this->inventoryService->getInventoryValuationReport();

        return view('inventory.valuation-report', compact('valuation'));
    }

    /**
     * Show low stock report.
     */
    public function lowStockReport(): View
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();
        $outOfStockProducts = $this->inventoryService->getOutOfStockProducts();

        return view('inventory.low-stock-report', compact(
            'lowStockProducts',
            'outOfStockProducts'
        ));
    }

    /**
     * Export inventory valuation to CSV.
     */
    public function exportValuationCsv()
    {
        $valuation = $this->inventoryService->getInventoryValuationReport();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=inventory-valuation-" . date('Y-m-d') . ".csv",
        );

        $columns = ['Product Code', 'Product Name', 'Quantity', 'Unit Cost', 'Cost Value', 'Unit Price', 'Retail Value', 'Profit Margin %'];

        $callback = function () use ($valuation, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($valuation['items'] as $item) {
                fputcsv($file, [
                    $item['product_code'],
                    $item['product_name'],
                    $item['quantity'],
                    $item['unit_cost'],
                    $item['cost_value'],
                    $item['unit_price'],
                    $item['retail_value'],
                    $item['profit_margin'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTALS']);
            fputcsv($file, [
                '',
                '',
                '',
                '',
                $valuation['total_cost_value'],
                '',
                $valuation['total_retail_value'],
                '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * View all movements (admin only).
     */
    public function allMovements(Request $request): View
    {
        $query = InventoryMovement::with(['product', 'creator'])->latest('movement_date');

        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('movement_type', $request->type);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $movements = $query->paginate(50);
        $products = Product::active()->get();

        return view('inventory.all-movements', compact('movements', 'products'));
    }
}