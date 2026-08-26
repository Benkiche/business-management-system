<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class FinancialReportController extends Controller
{
    protected FinancialReportService $financialService;

    public function __construct(FinancialReportService $financialService)
    {
        $this->middleware('auth');
        $this->financialService = $financialService;
    }

    /**
     * Display financial dashboard.
     */
    public function dashboard(): View
    {
        $dashboard = $this->financialService->getFinancialDashboard();
        $balanceSheet = $this->financialService->getBalanceSheet();

        return view('financial.dashboard', compact('dashboard', 'balanceSheet'));
    }

    /**
     * Display Profit & Loss statement.
     */
    public function profitAndLoss(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $pnl = $this->financialService->getProfitAndLoss($fromDate, $toDate);

        return view('financial.profit-loss', compact('pnl', 'fromDate', 'toDate'));
    }

    /**
     * Display expense report.
     */
    public function expenseReport(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $expenses = app(\App\Services\ExpenseService::class)->getExpenseSummary($fromDate, $toDate);

        return view('financial.expense-report', compact('expenses', 'fromDate', 'toDate'));
    }

    /**
     * Display balance sheet.
     */
    public function balanceSheet(): View
    {
        $balanceSheet = $this->financialService->getBalanceSheet();

        return view('financial.balance-sheet', compact('balanceSheet'));
    }

    /**
     * Compare two periods.
     */
    public function comparePeriods(Request $request): View
    {
        $currentFromDate = $request->input('current_from', now()->startOfMonth()->toDateString());
        $currentToDate = $request->input('current_to', now()->toDateString());
        $previousFromDate = $request->input('previous_from', now()->subMonth()->startOfMonth()->toDateString());
        $previousToDate = $request->input('previous_to', now()->subMonth()->endOfMonth()->toDateString());

        $comparison = $this->financialService->comparePeriods(
            $currentFromDate,
            $currentToDate,
            $previousFromDate,
            $previousToDate
        );

        return view('financial.compare-periods', compact(
            'comparison',
            'currentFromDate',
            'currentToDate',
            'previousFromDate',
            'previousToDate'
        ));
    }

    /**
     * Export financial report as CSV.
     */
    public function exportReport(Request $request): Response
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $csv = $this->financialService->exportFinancialReport($fromDate, $toDate);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="financial-report-' . date('Y-m-d') . '.csv"',
        ]);
    }
}