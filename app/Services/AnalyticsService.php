<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    /**
     * Get dashboard metrics.
     */
    public function getDashboardMetrics(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();
        $thisYear = now()->startOfYear()->toDateString();

        return [
            'today' => $this->getTodayMetrics($today),
            'this_month' => $this->getMonthMetrics($thisMonth, $today),
            'this_year' => $this->getYearMetrics($thisYear, $today),
            'all_time' => $this->getAllTimeMetrics(),
        ];
    }

    /**
     * Get today's metrics.
     */
    protected function getTodayMetrics(string $date): array
    {
        $expenses = Expense::approved()->whereDate('expense_date', $date)->get();
        $salesTotal = $this->salesTotalForDate($date);

        return [
            'sales_count' => $this->salesCountForDate($date),
            'sales_total' => $salesTotal,
            'expenses_total' => $expenses->sum('amount'),
            'profit' => $salesTotal - $expenses->sum('amount'),
        ];
    }

    /**
     * Get this month's metrics.
     */
    protected function getMonthMetrics(string $from, string $to): array
    {
        $expenses = Expense::approved()->dateRange($from, $to)->get();
        $sales = $this->salesInRange($from, $to);
        $salesTotal = $sales->sum('grand_total');

        return [
            'sales_count' => $sales->count(),
            'sales_total' => $salesTotal,
            'expenses_total' => $expenses->sum('amount'),
            'profit' => $salesTotal - $expenses->sum('amount'),
            'transactions_count' => $sales->count() + $expenses->count(),
        ];
    }

    /**
     * Get this year's metrics.
     */
    protected function getYearMetrics(string $from, string $to): array
    {
        $expenses = Expense::approved()->dateRange($from, $to)->get();
        $sales = $this->salesInRange($from, $to);
        $salesTotal = $sales->sum('grand_total');

        return [
            'sales_count' => $sales->count(),
            'sales_total' => $salesTotal,
            'expenses_total' => $expenses->sum('amount'),
            'profit' => $salesTotal - $expenses->sum('amount'),
        ];
    }

    /**
     * Get all time metrics.
     */
    protected function getAllTimeMetrics(): array
    {
        $customers = Customer::active()->get();
        $products = Product::active()->get();
        $sales = $this->salesInRange(null, null);

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('grand_total'),
            'total_customers' => $customers->count(),
            'total_products' => $products->count(),
            'total_outstanding' => $customers->sum('outstanding_balance'),
        ];
    }

    /**
     * Get sales trend data for chart.
     */
    public function getSalesTrend($fromDate, $toDate): array
    {
        $sales = $this->salesInRange($fromDate, $toDate);

        $trend = [];
        $currentDate = \Carbon\Carbon::parse($fromDate);
        $endDate = \Carbon\Carbon::parse($toDate);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $daySales = $sales->filter(function ($sale) use ($dateStr) {
                return $sale->sale_date->toDateString() === $dateStr;
            });

            $trend[] = [
                'date' => $currentDate->format('M d'),
                'sales' => $daySales->sum('grand_total'),
                'count' => $daySales->count(),
            ];

            $currentDate->addDay();
        }

        return $trend;
    }

    /**
     * Get revenue vs expenses comparison.
     */
    public function getRevenueVsExpenses($fromDate, $toDate): array
    {
        $data = [];
        $currentDate = \Carbon\Carbon::parse($fromDate);
        $endDate = \Carbon\Carbon::parse($toDate);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();

            $revenue = $this->salesTotalForDate($dateStr);

            $expenses = Expense::approved()
                ->whereDate('expense_date', $dateStr)
                ->sum('amount');

            $data[] = [
                'date' => $currentDate->format('M d'),
                'revenue' => $revenue,
                'expenses' => $expenses,
            ];

            $currentDate->addDay();
        }

        return $data;
    }

    /**
     * Get top selling products.
     */
    public function getTopProducts($fromDate, $toDate, $limit = 10): array
    {
        if (! $this->hasSalesTable()) {
            return [];
        }

        $sales = $this->salesInRange($fromDate, $toDate, ['items.product']);

        $products = [];

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productId = $item->product_id;
                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'name' => $item->product->name,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }
                $products[$productId]['quantity'] += $item->quantity;
                $products[$productId]['revenue'] += $item->line_total;
            }
        }

        // Sort by revenue descending
        usort($products, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return array_slice($products, 0, $limit);
    }

    /**
     * Get sales by category.
     */
    public function getSalesByCategory($fromDate, $toDate): array
    {
        if (! $this->hasSalesTable()) {
            return [];
        }

        $sales = $this->salesInRange($fromDate, $toDate, ['items.product.category']);

        $categories = [];

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $categoryName = $item->product->category->name;
                if (!isset($categories[$categoryName])) {
                    $categories[$categoryName] = [
                        'name' => $categoryName,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }
                $categories[$categoryName]['quantity'] += $item->quantity;
                $categories[$categoryName]['revenue'] += $item->line_total;
            }
        }

        // Sort by revenue descending
        usort($categories, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return $categories;
    }

    /**
     * Get payment method distribution.
     */
    public function getPaymentMethodDistribution($fromDate, $toDate): array
    {
        $sales = $this->salesInRange($fromDate, $toDate);

        return $sales->groupBy('payment_method')
            ->map(function ($items, $method) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $method)),
                    'count' => $items->count(),
                    'total' => $items->sum('grand_total'),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get top customers.
     */
    public function getTopCustomers($fromDate, $toDate, $limit = 10): array
    {
        if (! $this->hasSalesTable()) {
            return [];
        }

        $sales = $this->salesInRange($fromDate, $toDate, ['customer']);

        $customers = [];

        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;
            if (!isset($customers[$customerId])) {
                $customers[$customerId] = [
                    'name' => $sale->customer->name,
                    'purchases' => 0,
                    'total_spent' => 0,
                    'outstanding' => $sale->customer->outstanding_balance,
                ];
            }
            $customers[$customerId]['purchases'] += 1;
            $customers[$customerId]['total_spent'] += $sale->grand_total;
        }

        // Sort by total spent descending
        usort($customers, function ($a, $b) {
            return $b['total_spent'] <=> $a['total_spent'];
        });

        return array_slice($customers, 0, $limit);
    }

    /**
     * Get inventory status.
     */
    public function getInventoryStatus(): array
    {
        $products = Product::active()->get();

        return [
            'total_products' => $products->count(),
            'in_stock' => $products->where('quantity_on_hand', '>', 0)->count(),
            'out_of_stock' => $products->where('quantity_on_hand', 0)->count(),
            'low_stock' => $products->where('quantity_on_hand', '<=', DB::raw('minimum_stock_level'))
                ->where('quantity_on_hand', '>', 0)
                ->count(),
            'inventory_value' => $products->sum(function ($product) {
                return $product->quantity_on_hand * $product->purchase_price;
            }),
        ];
    }

    /**
     * Get customer credit analysis.
     */
    public function getCustomerCreditAnalysis(): array
    {
        $customers = Customer::active()->get();

        $totalCredit = $customers->sum('credit_limit');
        $usedCredit = $customers->sum('outstanding_balance');
        $availableCredit = $totalCredit - $usedCredit;

        return [
            'total_credit' => $totalCredit,
            'used_credit' => $usedCredit,
            'available_credit' => $availableCredit,
            'credit_usage_percent' => $totalCredit > 0 ? ($usedCredit / $totalCredit) * 100 : 0,
            'customers_at_limit' => $customers->filter(function ($customer) {
                return $customer->hasExceededCreditLimit();
            })->count(),
        ];
    }

    /**
     * Get monthly comparison.
     */
    public function getMonthlyComparison(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $from = $date->startOfMonth()->toDateString();
            $to = $date->endOfMonth()->toDateString();
            $monthName = $date->format('M');

            $sales = $this->salesTotalInRange($from, $to);
            $expenses = Expense::approved()->dateRange($from, $to)->sum('amount');

            $months[] = [
                'month' => $monthName,
                'revenue' => $sales,
                'expenses' => $expenses,
                'profit' => $sales - $expenses,
            ];
        }

        return $months;
    }

    /**
     * Get customer payment status.
     */
    public function getCustomerPaymentStatus(): array
    {
        $sales = $this->salesInRange(null, null);

        return [
            'paid_sales' => $sales->where('payment_status', 'paid')->count(),
            'partial_paid' => $sales->where('payment_status', 'partial')->count(),
            'unpaid_sales' => $sales->where('payment_status', 'pending')->count(),
            'total_outstanding' => $sales->sum(function ($sale) {
                return $sale->grand_total - $sale->amount_paid;
            }),
        ];
    }

    /**
     * Get inventory movement summary.
     */
    public function getInventoryMovementSummary($fromDate, $toDate): array
    {
        $movements = InventoryMovement::dateRange($fromDate, $toDate)->get();

        return [
            'stock_in' => $movements->where('movement_type', 'stock_in')->sum('quantity'),
            'stock_out' => $movements->where('movement_type', 'stock_out')->sum('quantity'),
            'sales' => $movements->where('movement_type', 'sale')->sum('quantity'),
            'adjustments' => $movements->where('movement_type', 'adjustment')->count(),
            'total_movements' => $movements->count(),
        ];
    }

    /**
     * Get sales performance by salesperson.
     */
    public function getSalesPerformance($fromDate, $toDate): array
    {
        if (! $this->hasSalesTable()) {
            return [];
        }

        $sales = $this->salesInRange($fromDate, $toDate, ['salesperson']);

        $salespeople = [];

        foreach ($sales as $sale) {
            $personId = $sale->salesperson_id;
            if (!isset($salespeople[$personId])) {
                $salespeople[$personId] = [
                    'name' => $sale->salesperson->name,
                    'sales_count' => 0,
                    'total_sales' => 0,
                    'average_sale' => 0,
                ];
            }
            $salespeople[$personId]['sales_count'] += 1;
            $salespeople[$personId]['total_sales'] += $sale->grand_total;
        }

        // Calculate averages and sort
        foreach ($salespeople as &$person) {
            $person['average_sale'] = $person['sales_count'] > 0 
                ? $person['total_sales'] / $person['sales_count'] 
                : 0;
        }

        usort($salespeople, function ($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });

        return $salespeople;
    }

    private function hasSalesTable(): bool
    {
        return Schema::hasTable('sales');
    }

    private function salesInRange($fromDate, $toDate, array $with = [])
    {
        if (! $this->hasSalesTable()) {
            return collect();
        }

        $query = Sale::query()->where('status', 'completed');
        if ($fromDate !== null && $toDate !== null) {
            $query->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        return $query->with($with)->get();
    }

    private function salesTotalForDate(string $date): float
    {
        return $this->hasSalesTable()
            ? (float) DB::table('sales')->where('status', 'completed')->whereDate('sale_date', $date)->sum('grand_total')
            : 0;
    }

    private function salesCountForDate(string $date): int
    {
        return $this->hasSalesTable()
            ? DB::table('sales')->where('status', 'completed')->whereDate('sale_date', $date)->count()
            : 0;
    }

    private function salesTotalInRange($fromDate, $toDate): float
    {
        if (! $this->hasSalesTable()) {
            return 0;
        }

        return (float) DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->sum('grand_total');
    }
}