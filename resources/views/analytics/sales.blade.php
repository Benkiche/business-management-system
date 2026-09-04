@extends('layouts.app')

@section('title', 'Sales Analytics')
@section('page-title', 'Sales Analytics')

@section('content')
<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" action="{{ route('analytics.sales') }}" class="card p-3 border-0 shadow-sm">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label fw-500">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label fw-500">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('reports.export', ['type' => 'sales', 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-success" title="Export CSV">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Top Row - Sales Trend & Payment Methods -->
<div class="row mb-4 g-4">
    <div class="col-12 col-lg-8">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-chart-line me-2 text-primary"></i>Sales Trend</h6>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; position: relative;">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-credit-card me-2 text-success"></i>Payment Methods</h6>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <div style="width: 100%; height: 280px; position: relative;">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row - Top Products & Sales by Category -->
<div class="row mb-4 g-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-star me-2 text-warning"></i>Top 10 Products by Revenue</h6>
            </div>
            <div class="card-body p-4">
                <div style="height: 350px; position: relative;">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-600"><i class="fas fa-layer-group me-2 text-info"></i>Sales by Category</h6>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <div style="width: 100%; height: 320px; position: relative;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Performance Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-600"><i class="fas fa-users me-2 text-secondary"></i>Sales Performance by Salesperson</h6>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="fw-600">Salesperson</th>
                        <th class="text-end fw-600">Sales Count</th>
                        <th class="text-end fw-600">Total Sales</th>
                        <th class="text-end fw-600">Average Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesPerformance as $person)
                        <tr>
                            <td><strong>{{ $person['name'] }}</strong></td>
                            <td class="text-end">{{ $person['sales_count'] }}</td>
                            <td class="text-end text-success fw-600">TZS {{ number_format($person['total_sales'], 2) }}</td>
                            <td class="text-end">TZS {{ number_format($person['average_sale'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Default Chart.js options for responsive charts
const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
};

// Sales Trend Chart
new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: @json(array_column($salesTrend, 'date')),
        datasets: [{
            label: 'Sales',
            data: @json(array_column($salesTrend, 'sales')),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#0d6efd',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
        }]
    },
    options: {
        ...defaultOptions,
        plugins: {
            legend: { display: false },
            filler: { propagate: true }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { 
                    callback: (v) => '$' + v.toFixed(0),
                    font: { size: 11 }
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});

// Payment Methods Pie Chart
new Chart(document.getElementById('paymentMethodChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: @json(array_column($paymentMethods, 'method')),
        datasets: [{
            data: @json(array_column($paymentMethods, 'total')),
            backgroundColor: ['#0d6efd', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
            borderColor: '#fff',
            borderWidth: 2,
        }]
    },
    options: {
        ...defaultOptions,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: { size: 12 },
                    usePointStyle: true,
                }
            }
        }
    }
});

// Top Products Bar Chart
new Chart(document.getElementById('topProductsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json(array_column($topProducts, 'name')),
        datasets: [{
            label: 'Revenue',
            data: @json(array_column($topProducts, 'revenue')),
            backgroundColor: '#28a745',
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y',
        ...defaultOptions,
        plugins: { 
            legend: { display: false }
        },
        scales: { 
            x: { 
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { 
                    callback: (v) => '$' + v.toFixed(0),
                    font: { size: 11 }
                } 
            },
            y: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});

// Sales by Category Doughnut Chart
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: @json(array_column($salesByCategory, 'name')),
        datasets: [{
            data: @json(array_column($salesByCategory, 'revenue')),
            backgroundColor: ['#0d6efd', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'],
            borderColor: '#fff',
            borderWidth: 2,
        }]
    },
    options: {
        ...defaultOptions,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: { size: 12 },
                    usePointStyle: true,
                }
            }
        }
    }
});
</script>
@endsection
@endsection