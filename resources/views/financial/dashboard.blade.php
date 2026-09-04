@extends('layouts.app')

@section('title', 'Financial Dashboard')
@section('page-title', 'Financial Dashboard')

@section('content')
<!-- Today's Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Today's Sales</small>
                <h5 class="mb-0 text-success">TZS {{ number_format($dashboard['today']['sales'], 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Today's Expenses</small>
                <h5 class="mb-0 text-danger">TZS {{ number_format($dashboard['today']['expenses'], 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Today's Profit</small>
                <h5 class="mb-0 {{ $dashboard['today']['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    TZS {{ number_format($dashboard['today']['profit'], 2) }}
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Profit Margin</small>
                <h5 class="mb-0">{{ number_format($dashboard['ratios']['current_profit_margin'], 1) }}%</h5>
            </div>
        </div>
    </div>
</div>

<!-- This Month -->
<div class="row mb-4">
    <div class="col-12">
        <h6 class="mb-3">This Month</h6>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Revenue</small>
                <h5 class="mb-0">TZS {{ number_format($dashboard['this_month']['sales'], 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Cost of Goods</small>
                <h5 class="mb-0">TZS {{ number_format($dashboard['this_month']['cost_of_goods'], 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Operating Expenses</small>
                <h5 class="mb-0">TZS {{ number_format($dashboard['this_month']['expenses'], 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Net Profit</small>
                <h5 class="mb-0 text-success">TZS {{ number_format($dashboard['this_month']['profit'], 2) }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Customer & Cash Flow -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Customer Status</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <small class="text-muted d-block">Active Customers</small>
                            <h4 class="mb-0">{{ $dashboard['customers']['total'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <small class="text-muted d-block">Outstanding</small>
                            <h4 class="mb-0 text-warning">TZS {{ number_format($dashboard['customers']['outstanding_debts'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <small class="text-muted d-block">Overdue</small>
                            <h4 class="mb-0 text-danger">TZS {{ number_format($dashboard['customers']['overdue_debts'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Cash Flow</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Total Paid</small>
                        <h5 class="mb-3">TZS {{ number_format($dashboard['cash_flow']['total_paid'], 2) }}</h5>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Total Received</small>
                        <h5 class="mb-3">TZS {{ number_format($dashboard['cash_flow']['total_received'], 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Financial Reports</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <a href="{{ route('financial.profit-loss') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-chart-line me-2"></i> Profit & Loss
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('financial.expenses') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-receipt me-2"></i> Expenses
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('financial.balance-sheet') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-scale-balanced me-2"></i> Balance Sheet
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('financial.compare') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-exchange me-2"></i> Compare Periods
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection