@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Inventory Operations</h5>
        </div>
        <div class="row g-2">
            <div class="col-md-3">
                <a href="{{ route('inventory.stock-in.form') }}" class="btn btn-success w-100">
                    <i class="fas fa-arrow-down me-2"></i> Stock In
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('inventory.stock-out.form') }}" class="btn btn-danger w-100">
                    <i class="fas fa-arrow-up me-2"></i> Stock Out
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('inventory.adjustment.form') }}" class="btn btn-warning w-100">
                    <i class="fas fa-sync me-2"></i> Adjust
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('inventory.valuation-report') }}" class="btn btn-primary w-100">
                    <i class="fas fa-chart-bar me-2"></i> Valuation
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Inventory Value</small>
                <h4 class="mb-0">${{ number_format($valuation['total_cost_value'], 2) }}</h4>
                <small class="text-success">at cost</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Retail Value</small>
                <h4 class="mb-0">${{ number_format($valuation['total_retail_value'], 2) }}</h4>
                <small class="text-info">selling prices</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Profit Potential</small>
                <h4 class="mb-0">${{ number_format($valuation['total_profit'], 2) }}</h4>
                <small class="text-success">if sold</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Products in Stock</small>
                <h4 class="mb-0">{{ $valuation['total_products'] }}</h4>
                <small class="text-muted">active items</small>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i> Out of Stock ({{ count($outOfStockProducts) }})
                </h6>
            </div>
            <div class="card-body">
                @if(count($outOfStockProducts) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outOfStockProducts as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item['product_name'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $item['product_code'] }}</small>
                                        </td>
                                        <td><small>{{ $item['supplier_name'] }}</small></td>
                                        <td class="text-end">
                                            <a href="{{ route('inventory.stock-in.form') }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0 text-center py-3">No products out of stock</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i> Low Stock ({{ count($lowStockProducts) }})
                </h6>
            </div>
            <div class="card-body">
                @if(count($lowStockProducts) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Current</th>
                                    <th>Min</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($lowStockProducts, 0, 5) as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item['product_name'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $item['product_code'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $item['quantity_on_hand'] }}</span>
                                        </td>
                                        <td>{{ $item['minimum_stock_level'] }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('inventory.stock-in.form') }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($lowStockProducts) > 5)
                        <a href="{{ route('inventory.low-stock-report') }}" class="btn btn-sm btn-outline-primary w-100">
                            View All Low Stock
                        </a>
                    @endif
                @else
                    <p class="text-muted mb-0 text-center py-3">No products with low stock</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Quick Reports</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <a href="{{ route('inventory.valuation-report') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-chart-line me-2"></i> Valuation Report
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('inventory.low-stock-report') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-exclamation me-2"></i> Low Stock Report
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('inventory.movements') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-list me-2"></i> All Movements
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('inventory.valuation-export-csv') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-download me-2"></i> Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection