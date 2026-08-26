<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    protected SaleService $saleService;
    protected ExpenseService $expenseService;

    public function __construct(SaleService $saleService, ExpenseService $expenseService)
    {
        $this->saleService = $saleService;
        $this->expenseService = $expenseService;
    }

    /**
     * Generate Profit & Loss statement.
     */
    public function getProfitAndLoss($fromDate, $toDate): array
    {
        $salesSummary = $this->saleService->getSalesSummary($fromDate, $toDate);
        $expenseSummary = $this->expenseService->getExpenseSummary($fromDate, $toDate);

        $revenue = $salesSummary['total_revenue'];
        $costOfGoodsSold = $salesSummary['total_cost'];
        $grossProfit = $revenue - $costOfGoodsSold;
        $operatingExpenses = $expenseSummary['total_expenses'];
        $netProfit = $grossProfit - $operatingExpenses;

        // Calculate margins
        $grossMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $netMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            'period_from' => $fromDate,
            'period_to' => $toDate,
            'revenue' => $revenue,
            'cost_of_goods_sold' => $costOfGoodsSold,
            'gross_profit' => $grossProfit,
            'gross_margin' => $grossMargin,
            'operating_expenses' => $operatingExpenses,
            'expenses_breakdown' => $expenseSummary['by_category'],
            'net_profit' => $netProfit,
            'net_margin' => $netMargin,
            'total_discount_given' => $salesSummary['total_discount'],
            'total_tax_collected' => $salesSummary['total_tax'],
        ];
    }

    /**
     * Get financial dashboard metrics.
     */
    public function getFinancialDashboard(): array
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $startOfYear = now()->startOfYear()->toDateString();

        // Today's metrics
        $todaySales = $this->saleService->getTodaySales($today);
        $todayExpenses = Expense::approved()
            ->whereDate('expense_date', $today)
            ->sum('amount');

        // This month's metrics
        $monthSales = $this->saleService->getSalesSummary($startOfMonth, $today);
        $monthExpenses = $this->expenseService->getExpenseSummary($startOfMonth, $today);

        // This year's metrics
        $yearSales = $this->saleService->getSalesSummary($startOfYear, $today);
        $yearExpenses = $this->expenseService->getExpenseSummary($startOfYear, $today);

        // Customer metrics
        $totalCustomers = DB::table('customers')->where('status', 'active')->count();
        $outstandingDebts = DB::table('customers')->sum('outstanding_balance');
        $overdueDebts = $this->getOverdueDebts();

        // Cash flow
        $totalPaid = $this->saleService->getTotalPaid();
        $totalReceived = $this->saleService->getTotalReceived();

        return [
            'today' => [
                'sales' => $todaySales,
                'expenses' => $todayExpenses,
                'profit' => $todaySales - $todayExpenses,
            ],
            'this_month' => [
                'sales' => $monthSales['total_revenue'],
                'expenses' => $monthExpenses['total_expenses'],
                'profit' => $monthSales['gross_profit'] - $monthExpenses['total_expenses'],
                'cost_of_goods' => $monthSales['total_cost'],
            ],
            'this_year' => [
                'sales' => $yearSales['total_revenue'],
                'expenses' => $yearExpenses['total_expenses'],
                'profit' => $yearSales['gross_profit'] - $yearExpenses['total_expenses'],
            ],
            'customers' => [
                'total' => $totalCustomers,
                'outstanding_debts' => $outstandingDebts,
                'overdue_debts' => $overdueDebts,
            ],
            'cash_flow' => [
                'total_paid' => $totalPaid,
                'total_received' => $totalReceived,
            ],
            'ratios' => [
                'current_profit_margin' => $monthSales['profit_margin'],
                'current_expense_ratio' => $monthSales['total_revenue'] > 0 
                    ? ($monthExpenses['total_expenses'] / $monthSales['total_revenue']) * 100 
                    : 0,
            ],
        ];
    }

    /**
     * Get overdue customer debts.
     */
    public function getOverdueDebts(): float
    {
        return $this->saleService->getOverdueDebts(now()->toDateString());
    }

    /**
     * Get balance sheet snapshot.
     */
    public function getBalanceSheet(): array
    {
        // Assets
        $inventoryValue = DB::table('products')
            ->where('status', 'active')
            ->sum(DB::raw('quantity_on_hand * purchase_price'));

        $customerDebts = DB::table('customers')->sum('outstanding_balance');
        $cashReceived = $this->saleService->getTotalPaid();

        // Liabilities
        $supplierDebts = DB::table('suppliers')->sum('outstanding_balance');

        // Calculate equity (simple)
        $totalAssets = $inventoryValue + $customerDebts + $cashReceived;
        $totalLiabilities = $supplierDebts;
        $equity = $totalAssets - $totalLiabilities;

        return [
            'assets' => [
                'inventory' => $inventoryValue,
                'customer_receivables' => $customerDebts,
                'cash' => $cashReceived,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'supplier_payables' => $supplierDebts,
                'total' => $totalLiabilities,
            ],
            'equity' => $equity,
        ];
    }

    /**
     * Export financial summary as CSV.
     */
    public function exportFinancialReport($fromDate, $toDate): string
    {
        $pnl = $this->getProfitAndLoss($fromDate, $toDate);

        $csv = "Financial Report\n";
        $csv .= "Period: {$fromDate} to {$toDate}\n\n";
        $csv .= "INCOME STATEMENT\n";
        $csv .= "Revenue," . number_format($pnl['revenue'], 2) . "\n";
        $csv .= "Cost of Goods Sold," . number_format($pnl['cost_of_goods_sold'], 2) . "\n";
        $csv .= "Gross Profit," . number_format($pnl['gross_profit'], 2) . "\n";
        $csv .= "Gross Margin %," . number_format($pnl['gross_margin'], 2) . "%\n";
        $csv .= "\nOPERATING EXPENSES\n";

        foreach ($pnl['expenses_breakdown'] as $expense) {
            $csv .= "{$expense['category']}," . number_format($expense['total'], 2) . "\n";
        }

        $csv .= "\nTotal Operating Expenses," . number_format($pnl['operating_expenses'], 2) . "\n";
        $csv .= "NET PROFIT," . number_format($pnl['net_profit'], 2) . "\n";
        $csv .= "Net Margin %," . number_format($pnl['net_margin'], 2) . "%\n";

        return $csv;
    }

    /**
     * Get comparison metrics between periods.
     */
    public function comparePeriods($currentFromDate, $currentToDate, $previousFromDate, $previousToDate): array
    {
        $current = $this->getProfitAndLoss($currentFromDate, $currentToDate);
        $previous = $this->getProfitAndLoss($previousFromDate, $previousToDate);

        $calculateChange = function ($current, $previous) {
            if ($previous == 0) return 0;
            return (($current - $previous) / $previous) * 100;
        };

        return [
            'current_period' => $current,
            'previous_period' => $previous,
            'changes' => [
                'revenue_change_percent' => $calculateChange($current['revenue'], $previous['revenue']),
                'profit_change_percent' => $calculateChange($current['net_profit'], $previous['net_profit']),
                'expense_change_percent' => $calculateChange($current['operating_expenses'], $previous['operating_expenses']),
            ],
        ];
    }
}