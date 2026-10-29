<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;
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
        $dashboard = Cache::remember('dashboard:' . auth()->id() . ':' . now()->toDateString(), 300, function () {
            return [
                'metrics' => $this->analyticsService->getDashboardMetrics(),
                'topProducts' => $this->analyticsService->getTopProducts(now()->startOfMonth()->toDateString(), now()->toDateString(), 5),
                'topCustomers' => $this->analyticsService->getTopCustomers(now()->startOfMonth()->toDateString(), now()->toDateString(), 5),
                'inventoryStatus' => $this->analyticsService->getInventoryStatus(),
                'paymentStatus' => $this->analyticsService->getCustomerPaymentStatus(),
                'salesTrend' => $this->analyticsService->getSalesTrend(now()->subDays(30)->toDateString(), now()->toDateString()),
                'revenueVsExpenses' => $this->analyticsService->getRevenueVsExpenses(now()->subDays(30)->toDateString(), now()->toDateString()),
                'monthlyComparison' => $this->analyticsService->getMonthlyComparison(),
            ];
        });

        $metrics = $dashboard['metrics'];
        $topProducts = $dashboard['topProducts'];
        $topCustomers = $dashboard['topCustomers'];
        $inventoryStatus = $dashboard['inventoryStatus'];
        $paymentStatus = $dashboard['paymentStatus'];
        $salesTrend = $dashboard['salesTrend'];
        $revenueVsExpenses = $dashboard['revenueVsExpenses'];
        $monthlyComparison = $dashboard['monthlyComparison'];

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