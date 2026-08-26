@extends('layouts.app')

@section('title', 'All Inventory Movements')
@section('page-title', 'All Inventory Movements')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('inventory.movements') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                        <option value="stock_out" {{ request('type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sale</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Cost</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr>
                            <td><small>{{ $movement->movement_date->format('M d, Y H:i') }}</small></td>
                            <td>
                                <a href="{{ route('inventory.product-history', $movement->product) }}" class="text-decoration-none">
                                    {{ $movement->product->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ 
                                    $movement->movement_type === 'stock_in' ? 'bg-success' :
                                    ($movement->movement_type === 'stock_out' ? 'bg-danger' :
                                    ($movement->movement_type === 'adjustment' ? 'bg-warning' : 'bg-info'))
                                }}">
                                    {{ $movement->getTypeLabel() }}
                                </span>
                            </td>
                            <td class="text-end fw-bold">
                                <span class="{{ $movement->isIncrease() ? 'text-success' : 'text-danger' }}">
                                    {{ $movement->isIncrease() ? '+' : '' }}{{ $movement->quantity }}
                                </span>
                            </td>
                            <td class="text-end">{{ $movement->unit_cost ? '$' . number_format($movement->unit_cost, 2) : '-' }}</td>
                            <td><small>{{ $movement->reference_type ?? '-' }}</small></td>
                            <td><small>{{ Str::limit($movement->notes, 30) }}</small></td>
                            <td><small>{{ $movement->creator->name }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $movements->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection