@extends('layouts.app')

@section('title', 'Sales')
@section('page-title', 'Sales')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sales List</h5>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> New Sale
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-3 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search invoice or customer" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['draft', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        @if($sales->count())
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ $sale->customer?->name }}</td>
                                <td>{{ $sale->sale_date?->format('M d, Y') }}</td>
                                <td class="text-end">${{ number_format($sale->grand_total, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($sale->status) }}</span></td>
                                <td class="text-end"><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $sales->links('pagination::bootstrap-5') }}
        @else
            <p class="text-center text-muted py-5 mb-0">No sales found.</p>
        @endif
    </div>
</div>
@endsection