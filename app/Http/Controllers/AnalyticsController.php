<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected ReportService $reportService;

    public function __construct(AnalyticsService $analyticsService, ReportService $reportService)
    {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
        $this->reportService = $reportService;
    }

    /**
     * Display sales analytics.
     */
    public function sales(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $salesTrend = $this->analyticsService->getSalesTrend($fromDate, $toDate);
        $topProducts = $this->analyticsService->getTopProducts($fromDate, $toDate);
        $salesByCategory = $this->analyticsService->getSalesByCategory($fromDate, $toDate);
        $paymentMethods = $this->analyticsService->getPaymentMethodDistribution($fromDate, $toDate);
        $salesPerformance = $this->analyticsService->getSalesPerformance($fromDate, $toDate);

        return view('analytics.sales', compact(
            'salesTrend',
            'topProducts',
            'salesByCategory',
            'paymentMethods',
            'salesPerformance',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Display inventory analytics.
     */
    public function inventory(): View
    {
        $inventoryStatus = $this->analyticsService->getInventoryStatus();
        $movement = $this->analyticsService->getInventoryMovementSummary(
            now()->startOfMonth()->toDateString(),
            now()->toDateString()
        );

        return view('analytics.inventory', compact('inventoryStatus', 'movement'));
    }

    /**
     * Display customer analytics.
     */
    public function customers(): View
    {
        $topCustomers = $this->analyticsService->getTopCustomers(now()->startOfMonth()->toDateString(), now()->toDateString(), 20);
        $creditAnalysis = $this->analyticsService->getCustomerCreditAnalysis();

        return view('analytics.customers', compact('topCustomers', 'creditAnalysis'));
    }

    /**
     * Display sales report.
     */
    public function salesReport(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $report = $this->reportService->generateSalesReport($fromDate, $toDate);

        return view('reports.sales', compact('report', 'fromDate', 'toDate'));
    }

    /**
     * Display customer report.
     */
    public function customerReport(): View
    {
        $report = $this->reportService->generateCustomerReport();

        return view('reports.customers', compact('report'));
    }

    /**
     * Display inventory report.
     */
    public function inventoryReport(): View
    {
        $report = $this->reportService->generateInventoryReport();

        return view('reports.inventory', compact('report'));
    }

    /**
     * Export report as CSV.
     */
    public function exportReport(Request $request): Response
    {
        $type = $request->input('type', 'sales');
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        if ($type === 'sales') {
            $data = $this->reportService->generateSalesReport($fromDate, $toDate);
        } elseif ($type === 'customers') {
            $data = $this->reportService->generateCustomerReport();
        } elseif ($type === 'inventory') {
            $data = $this->reportService->generateInventoryReport();
        } else {
            return response('Invalid report type', 400);
        }

        $csv = $this->reportService->exportReportCsv($type, $data);
        $filename = "report-{$type}-" . date('Y-m-d') . ".csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}