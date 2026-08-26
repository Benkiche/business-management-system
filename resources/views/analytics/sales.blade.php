@extends('layouts.app')

@section('title', 'Sales Analytics')
@section('page-title', 'Sales Analytics')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" action="{{ route('analytics.sales') }}" class="card p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                    <a href="{{ route('reports.export', ['type' => 'sales', 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-success" title="Export CSV">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Sales Trend</h6>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Payment Methods</h6>
            </div>
            <div class="card-body">
                <canvas id="paymentMethodChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Top 10 Products by Revenue</h6>
            </div>
            <div class="card-body">
                <canvas id="topProductsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Sales by Category</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Sales Performance -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0">Sales Performance by Salesperson</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Salesperson</th>
                        <th class="text-end">Sales Count</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">Average Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesPerformance as $person)
                        <tr>
                            <td><strong>{{ $person['name'] }}</strong></td>
                            <td class="text-end">{{ $person['sales_count'] }}</td>
                            <td class="text-end">${{ number_format($person['total_sales'], 2) }}</td>
                            <td class="text-end">${{ number_format($person['average_sale'], 2) }}</td>
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
// Sales Trend
new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: @json(array_column($salesTrend, 'date')),
        datasets: [{
            label: 'Sales',
            data: @json(array_column($salesTrend, 'sales')),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: (v) => '$' + v.toFixed(0) }
            }
        }
    }
});

// Payment Methods
new Chart(document.getElementById('paymentMethodChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: @json(array_column($paymentMethods, 'method')),
        datasets: [{
            data: @json(array_column($paymentMethods, 'total')),
            backgroundColor: ['#0d6efd', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Top Products
new Chart(document.getElementById('topProductsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json(array_column($topProducts, 'name')),
        datasets: [{
            label: 'Revenue',
            data: @json(array_column($topProducts, 'revenue')),
            backgroundColor: '#28a745',
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { ticks: { callback: (v) => '$' + v.toFixed(0) } } }
    }
});

// Categories
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: @json(array_column($salesByCategory, 'name')),
        datasets: [{
            data: @json(array_column($salesByCategory, 'revenue')),
            backgroundColor: ['#0d6efd', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endsection
@endsection