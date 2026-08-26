<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the main dashboard.
     */
    public function index(): View
    {
        $metrics = $this->analyticsService->getDashboardMetrics();
        $topProducts = $this->analyticsService->getTopProducts(now()->startOfMonth()->toDateString(), now()->toDateString(), 5);
        $topCustomers = $this->analyticsService->getTopCustomers(now()->startOfMonth()->toDateString(), now()->toDateString(), 5);
        $inventoryStatus = $this->analyticsService->getInventoryStatus();
        $paymentStatus = $this->analyticsService->getCustomerPaymentStatus();
        $salesTrend = $this->analyticsService->getSalesTrend(now()->subDays(30)->toDateString(), now()->toDateString());
        $revenueVsExpenses = $this->analyticsService->getRevenueVsExpenses(now()->subDays(30)->toDateString(), now()->toDateString());
        $monthlyComparison = $this->analyticsService->getMonthlyComparison();

        return view('dashboard.index', compact(
            'metrics',
            'topProducts',
            'topCustomers',
            'inventoryStatus',
            'paymentStatus',
            'salesTrend',
            'revenueVsExpenses',
            'monthlyComparison'
        ));
    }
}