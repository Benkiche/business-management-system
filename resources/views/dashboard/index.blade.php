@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="dashboard-page">
<!-- Today's Metrics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card dashboard-metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted d-block">Today's Sales</small>
                        <h4 class="mb-0 text-success">${{ number_format($metrics['today']['sales_total'], 2) }}</h4>
                        <small class="text-muted">{{ $metrics['today']['sales_count'] }} transactions</small>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card dashboard-metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted d-block">Today's Expenses</small>
                        <h4 class="mb-0 text-danger">${{ number_format($metrics['today']['expenses_total'], 2) }}</h4>
                        <small class="text-muted">Cost of operations</small>
                    </div>
                    <i class="fas fa-receipt fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card dashboard-metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted d-block">Today's Profit</small>
                        <h4 class="mb-0 {{ $metrics['today']['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($metrics['today']['profit'], 2) }}
                        </h4>
                        <small class="text-muted">Net income</small>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50" style="color: #28a745;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card dashboard-metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted d-block">This Month</small>
                        <h4 class="mb-0 text-info">${{ number_format($metrics['this_month']['sales_total'], 2) }}</h4>
                        <small class="text-muted">{{ $metrics['this_month']['sales_count'] }} sales</small>
                    </div>
                    <i class="fas fa-calendar fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Sales Trend (Last 30 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Payment Status</h6>
            </div>
            <div class="card-body">
                <canvas id="paymentStatusChart" height="200"></canvas>
                <hr>
                <div class="row text-center small">
                    <div class="col">
                        <strong>{{ $paymentStatus['paid_sales'] }}</strong>
                        <small class="d-block text-muted">Paid</small>
                    </div>
                    <div class="col">
                        <strong>{{ $paymentStatus['partial_paid'] }}</strong>
                        <small class="d-block text-muted">Partial</small>
                    </div>
                    <div class="col">
                        <strong>{{ $paymentStatus['unpaid_sales'] }}</strong>
                        <small class="d-block text-muted">Unpaid</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue vs Expenses -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Revenue vs Expenses (Last 30 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueVsExpensesChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">12-Month Overview</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyComparisonChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Products & Customers -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Top 5 Products (This Month)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr>
                                    <td>
                                        <small>{{ $product['name'] }}</small>
                                    </td>
                                    <td class="text-end">
                                        <small>${{ number_format($product['revenue'], 2) }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">
                                        No sales data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('analytics.sales') }}" class="btn btn-sm btn-outline-primary mt-2">
                    View All Analytics
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Top 5 Customers (This Month)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th class="text-end">Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                                <tr>
                                    <td>
                                        <small>{{ $customer['name'] }}</small>
                                    </td>
                                    <td class="text-end">
                                        <small>${{ number_format($customer['total_spent'], 2) }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">
                                        No customer data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('analytics.customers') }}" class="btn btn-sm btn-outline-primary mt-2">
                    View All Customers
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Inventory & Quick Stats -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Inventory Status</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">Total Products</small>
                            <h5 class="mb-0">{{ $inventoryStatus['total_products'] }}</h5>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">In Stock</small>
                            <h5 class="mb-0 text-success">{{ $inventoryStatus['in_stock'] }}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">Low Stock</small>
                            <h5 class="mb-0 text-warning">{{ $inventoryStatus['low_stock'] }}</h5>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Out of Stock</small>
                            <h5 class="mb-0 text-danger">{{ $inventoryStatus['out_of_stock'] }}</h5>
                        </div>
                    </div>
                </div>
                <hr>
                <small class="text-muted d-block">Inventory Value</small>
                <h5>${{ number_format($inventoryStatus['inventory_value'], 2) }}</h5>
                <a href="{{ route('analytics.inventory') }}" class="btn btn-sm btn-outline-primary mt-2">
                    View Details
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card dashboard-panel h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @if (Route::has('sales.index'))
                        <a href="{{ route('sales.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-cart me-2"></i> View Sales
                        </a>
                    @endif
                    <a href="{{ route('expenses.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-receipt me-2"></i> View Expenses
                    </a>
                    <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> View Customers
                    </a>
                    <a href="{{ route('analytics.sales') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i> Sales Analytics
                    </a>
                    <a href="{{ route('financial.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-line me-2"></i> Financial Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@section('css')
<style>
    .dashboard-page .row > [class*="col-"] {
        display: flex;
    }

    .dashboard-page .card {
        width: 100%;
        margin-bottom: 0;
        border: 1px solid rgba(13, 110, 253, 0.08);
        box-shadow: 0 6px 18px rgba(31, 45, 61, 0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dashboard-page .dashboard-metric-card {
        position: relative;
        overflow: hidden;
    }

    .dashboard-page .dashboard-metric-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--primary-color);
    }

    .dashboard-page .dashboard-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(31, 45, 61, 0.12);
    }

    .dashboard-page .dashboard-metric-card .card-body {
        padding: 1.25rem;
    }

    .dashboard-page .dashboard-metric-card h4 {
        margin-top: 0.35rem !important;
        font-size: clamp(1.2rem, 2vw, 1.55rem);
    }

    .dashboard-page .card-header {
        padding: 1rem 1.25rem;
        border-bottom-color: rgba(0, 0, 0, 0.06);
    }

    .dashboard-page .card-header h6 {
        color: #344054;
        font-weight: 600;
    }

    .dashboard-page .dashboard-panel .card-body {
        padding: 1.25rem;
    }

    @media (max-width: 575.98px) {
        .dashboard-page .dashboard-metric-card .card-body,
        .dashboard-page .dashboard-panel .card-body {
            padding: 1rem;
        }

        .dashboard-page canvas {
            max-height: 220px;
        }
    }
</style>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Sales Trend Chart
const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(salesTrendCtx, {
    type: 'line',
    data: {
        labels: @json(array_column($salesTrend, 'date')),
        datasets: [{
            label: 'Daily Sales',
            data: @json(array_column($salesTrend, 'sales')),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#0d6efd',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(0);
                    }
                }
            }
        }
    }
});

// Payment Status Chart
const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
new Chart(paymentStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Paid', 'Partial', 'Unpaid'],
        datasets: [{
            data: [
                {{ $paymentStatus['paid_sales'] }},
                {{ $paymentStatus['partial_paid'] }},
                {{ $paymentStatus['unpaid_sales'] }}
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Revenue vs Expenses Chart
const revenueVsExpensesCtx = document.getElementById('revenueVsExpensesChart').getContext('2d');
new Chart(revenueVsExpensesCtx, {
    type: 'bar',
    data: {
        labels: @json(array_column($revenueVsExpenses, 'date')),
        datasets: [
            {
                label: 'Revenue',
                data: @json(array_column($revenueVsExpenses, 'revenue')),
                backgroundColor: '#28a745',
            },
            {
                label: 'Expenses',
                data: @json(array_column($revenueVsExpenses, 'expenses')),
                backgroundColor: '#dc3545',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(0);
                    }
                }
            }
        }
    }
});

// Monthly Comparison Chart
const monthlyComparisonCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
new Chart(monthlyComparisonCtx, {
    type: 'bar',
    data: {
        labels: @json(array_column($monthlyComparison, 'month')),
        datasets: [{
            label: 'Profit',
            data: @json(array_column($monthlyComparison, 'profit')),
            backgroundColor: function(context) {
                const value = context.raw;
                return value >= 0 ? '#28a745' : '#dc3545';
            },
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + (value/1000).toFixed(0) + 'K';
                    }
                }
            }
        }
    }
});
</script>
@endsection
@endsection