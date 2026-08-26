@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $fromDate }}" required>
            </div>
            <div class="col-md-3">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $toDate }}" required>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="{{ route('reports.export', ['type' => 'sales', 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-success" title="Export CSV" aria-label="Export CSV"><i class="fas fa-download"></i></a>
            </div>
            <div class="col-md-3 text-md-end text-muted small">
                {{ $report['period_from'] }} to {{ $report['period_to'] }}
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted d-block">Completed Sales</small><h3 class="mb-0">{{ number_format($report['total_sales']) }}</h3></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted d-block">Total Revenue</small><h3 class="mb-0">${{ number_format($report['total_revenue'], 2) }}</h3></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted d-block">Gross Profit</small><h3 class="mb-0 text-success">${{ number_format($report['gross_profit'], 2) }}</h3></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted d-block">Profit Margin</small><h3 class="mb-0">{{ number_format($report['profit_margin'], 2) }}%</h3></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Report Summary</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><small class="text-muted d-block">Cost of Goods</small><strong>${{ number_format($report['total_cost'], 2) }}</strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Average Sale</small><strong>${{ number_format($report['average_sale'], 2) }}</strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Discounts Given</small><strong>${{ number_format($report['total_discount'], 2) }}</strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Tax Collected</small><strong>${{ number_format($report['total_tax'], 2) }}</strong></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">Completed Sales</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Salesperson</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['details'] as $sale)
                        <tr>
                            <td>{{ $sale['invoice_number'] }}</td>
                            <td>{{ substr($sale['sale_date'], 0, 10) }}</td>
                            <td>{{ data_get($sale, 'customer.name', 'Unknown') }}</td>
                            <td>{{ data_get($sale, 'salesperson.name', 'Unknown') }}</td>
                            <td class="text-end">${{ number_format($sale['grand_total'], 2) }}</td>
                            <td class="text-end"><a href="{{ route('sales.show', $sale['id']) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No completed sales found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
