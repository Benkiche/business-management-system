<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Expense;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Generate sales report.
     */
    public function generateSalesReport($fromDate, $toDate): array
    {
        if (! Schema::hasTable('sales')) {
            return [
                'period_from' => $fromDate,
                'period_to' => $toDate,
                'total_sales' => 0,
                'total_revenue' => 0,
                'total_cost' => 0,
                'gross_profit' => 0,
                'profit_margin' => 0,
                'total_discount' => 0,
                'total_tax' => 0,
                'average_sale' => 0,
                'details' => [],
            ];
        }

        $sales = Sale::completed()
            ->dateRange($fromDate, $toDate)
            ->with('customer', 'salesperson', 'items')
            ->get();

        $totalRevenue = $sales->sum('grand_total');
        $totalDiscount = $sales->sum('discount_amount');
        $totalTax = $sales->sum('tax_amount');
        $totalCost = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $totalCost += $item->quantity * $item->product->purchase_price;
            }
        }

        $grossProfit = $totalRevenue - $totalCost;

        return [
            'period_from' => $fromDate,
            'period_to' => $toDate,
            'total_sales' => $sales->count(),
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'average_sale' => $sales->count() > 0 ? $totalRevenue / $sales->count() : 0,
            'details' => $sales->toArray(),
        ];
    }

    /**
     * Generate customer report.
     */
    public function generateCustomerReport(): array
    {
        $customers = Customer::active()
            ->with('sales', 'payments')
            ->get();

        return [
            'total_customers' => $customers->count(),
            'total_outstanding' => $customers->sum('outstanding_balance'),
            'average_outstanding' => $customers->count() > 0 
                ? $customers->sum('outstanding_balance') / $customers->count() 
                : 0,
            'customers_with_debt' => $customers->filter(function ($c) {
                return $c->outstanding_balance > 0;
            })->count(),
            'details' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'total_purchases' => $customer->sales->count(),
                    'total_spent' => $customer->sales->sum('grand_total'),
                    'outstanding_balance' => $customer->outstanding_balance,
                    'credit_limit' => $customer->credit_limit,
                    'at_limit' => $customer->outstanding_balance > $customer->credit_limit,
                ];
            })->toArray(),
        ];
    }

    /**
     * Generate inventory report.
     */
    public function generateInventoryReport(): array
    {
        $products = Product::active()
            ->with('category', 'supplier')
            ->get();

        $lowStock = $products->filter(function ($p) {
            return $p->quantity_on_hand <= $p->minimum_stock_level && $p->quantity_on_hand > 0;
        });

        $outOfStock = $products->filter(function ($p) {
            return $p->quantity_on_hand == 0;
        });

        return [
            'total_products' => $products->count(),
            'total_value' => $products->sum(function ($p) {
                return $p->quantity_on_hand * $p->purchase_price;
            }),
            'out_of_stock_count' => $outOfStock->count(),
            'low_stock_count' => $lowStock->count(),
            'out_of_stock' => $outOfStock->map(function ($p) {
                return [
                    'code' => $p->product_code,
                    'name' => $p->name,
                    'category' => $p->category->name,
                ];
            })->toArray(),
            'low_stock' => $lowStock->map(function ($p) {
                return [
                    'code' => $p->product_code,
                    'name' => $p->name,
                    'quantity' => $p->quantity_on_hand,
                    'minimum' => $p->minimum_stock_level,
                    'value' => $p->quantity_on_hand * $p->purchase_price,
                ];
            })->toArray(),
        ];
    }

    /**
     * Export report as CSV.
     */
    public function exportReportCsv($type, $data): string
    {
        if ($type === 'sales') {
            return $this->exportSalesReportCsv($data);
        } elseif ($type === 'customers') {
            return $this->exportCustomerReportCsv($data);
        } elseif ($type === 'inventory') {
            return $this->exportInventoryReportCsv($data);
        }

        return '';
    }

    protected function exportSalesReportCsv($data): string
    {
        $csv = "Sales Report\n";
        $csv .= "Period: {$data['period_from']} to {$data['period_to']}\n\n";
        $csv .= "Total Sales,{$data['total_sales']}\n";
        $csv .= "Total Revenue,$" . number_format($data['total_revenue'], 2) . "\n";
        $csv .= "Total Cost,$" . number_format($data['total_cost'], 2) . "\n";
        $csv .= "Gross Profit,$" . number_format($data['gross_profit'], 2) . "\n";
        $csv .= "Profit Margin," . number_format($data['profit_margin'], 2) . "%\n\n";
        $csv .= "Invoice,Date,Customer,Amount,Profit\n";

        foreach ($data['details'] as $sale) {
            $csv .= "{$sale['invoice_number']},{$sale['sale_date']},{$sale['customer']['name']},";
            $csv .= "$" . number_format($sale['grand_total'], 2) . "\n";
        }

        return $csv;
    }

    protected function exportCustomerReportCsv($data): string
    {
        $csv = "Customer Report\n";
        $csv .= "Total Customers,{$data['total_customers']}\n";
        $csv .= "Total Outstanding,$" . number_format($data['total_outstanding'], 2) . "\n\n";
        $csv .= "Name,Email,Phone,Total Purchases,Total Spent,Outstanding,Credit Limit,At Limit\n";

        foreach ($data['details'] as $customer) {
            $csv .= "{$customer['name']},{$customer['email']},{$customer['phone']},";
            $csv .= "{$customer['total_purchases']},$" . number_format($customer['total_spent'], 2) . ",";
            $csv .= "$" . number_format($customer['outstanding_balance'], 2) . ",";
            $csv .= "$" . number_format($customer['credit_limit'], 2) . ",";
            $csv .= ($customer['at_limit'] ? 'Yes' : 'No') . "\n";
        }

        return $csv;
    }

    protected function exportInventoryReportCsv($data): string
    {
        $csv = "Inventory Report\n";
        $csv .= "Total Products,{$data['total_products']}\n";
        $csv .= "Total Value,$" . number_format($data['total_value'], 2) . "\n\n";
        $csv .= "OUT OF STOCK PRODUCTS\n";
        $csv .= "Code,Name,Category\n";

        foreach ($data['out_of_stock'] as $product) {
            $csv .= "{$product['code']},{$product['name']},{$product['category']}\n";
        }

        $csv .= "\nLOW STOCK PRODUCTS\n";
        $csv .= "Code,Name,Quantity,Minimum,Value\n";

        foreach ($data['low_stock'] as $product) {
            $csv .= "{$product['code']},{$product['name']},{$product['quantity']},";
            $csv .= "{$product['minimum']},$" . number_format($product['value'], 2) . "\n";
        }

        return $csv;
    }
}