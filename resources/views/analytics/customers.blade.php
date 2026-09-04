@extends('layouts.app')

@section('title', 'Customer Analytics')
@section('page-title', 'Customer Analytics')

@section('content')
<!-- Credit Analysis -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Credit Limit</small>
                <h5 class="mb-0">TZS {{ number_format($creditAnalysis['total_credit'], 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Used Credit</small>
                <h5 class="mb-0 text-warning">TZS {{ number_format($creditAnalysis['used_credit'], 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Available Credit</small>
                <h5 class="mb-0 text-success">TZS {{ number_format($creditAnalysis['available_credit'], 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Credit Usage</small>
                <h5 class="mb-0">{{ number_format($creditAnalysis['credit_usage_percent'], 1) }}%</h5>
            </div>
        </div>
    </div>
</div>

<!-- Top Customers -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0">Top Customers (This Month)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Customer Name</th>
                        <th class="text-end">Purchases</th>
                        <th class="text-end">Total Spent</th>
                        <th class="text-end">Outstanding</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCustomers as $customer)
                        <tr>
                            <td>{{ $customer['name'] }}</td>
                            <td class="text-end">{{ $customer['purchases'] }}</td>
                            <td class="text-end">TZS {{ number_format($customer['total_spent'], 2) }}</td>
                            <td class="text-end">TZS {{ number_format($customer['outstanding'], 2) }}</td>
                            <td class="text-center">
                                @if($customer['outstanding'] == 0)
                                    <span class="badge bg-success">Settled</span>
                                @else
                                    <span class="badge bg-warning">Outstanding</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($creditAnalysis['customers_at_limit'] > 0)
    <div class="alert alert-danger mt-4">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Alert:</strong> {{ $creditAnalysis['customers_at_limit'] }} customer(s) have reached or exceeded their credit limits.
    </div>
@endif
@endsection