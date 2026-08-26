@extends('layouts.app')

@section('title', 'Inventory Analytics')
@section('page-title', 'Inventory Analytics')

@section('content')
<!-- Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Products</small>
                <h5 class="mb-0">{{ $inventoryStatus['total_products'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">In Stock</small>
                <h5 class="mb-0 text-success">{{ $inventoryStatus['in_stock'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Low Stock</small>
                <h5 class="mb-0 text-warning">{{ $inventoryStatus['low_stock'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Out of Stock</small>
                <h5 class="mb-0 text-danger">{{ $inventoryStatus['out_of_stock'] }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Value & Movement -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Inventory Valuation</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h3>${{ number_format($inventoryStatus['inventory_value'], 2) }}</h3>
                    <small class="text-muted">Total Inventory Value</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">This Month Movement</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <small class="text-muted d-block">Stock In</small>
                        <h5>{{ $movement['stock_in'] }}</h5>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block">Stock Out</small>
                        <h5>{{ $movement['stock_out'] }}</h5>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block">Sold</small>
                        <h5>{{ $movement['sales'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Inventory Status:</strong> You have {{ $inventoryStatus['out_of_stock'] }} out of stock items and {{ $inventoryStatus['low_stock'] }} low stock items.
    <a href="{{ route('inventory.index') }}" class="alert-link">View Details</a>
</div>
@endsection