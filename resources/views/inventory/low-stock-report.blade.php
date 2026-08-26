@extends('layouts.app')

@section('title', 'Low Stock Report')
@section('page-title', 'Low Stock & Out of Stock Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-ban me-2"></i> Out of Stock ({{ count($outOfStockProducts) }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(count($outOfStockProducts) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Min Level</th>
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
                                                <td class="text-end">{{ $item['minimum_stock_level'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No out of stock products</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-warning">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i> Low Stock ({{ count($lowStockProducts) }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(count($lowStockProducts) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Current</th>
                                            <th class="text-end">Min</th>
                                            <th class="text-end">Shortage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lowStockProducts as $item)
                                            <tr>
                                                <td>
                                                    <strong>{{ $item['product_name'] }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $item['product_code'] }}</small>
                                                </td>
                                                <td class="text-end">{{ $item['quantity_on_hand'] }}</td>
                                                <td class="text-end">{{ $item['minimum_stock_level'] }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-danger">{{ $item['shortage'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No low stock products</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('inventory.stock-in.form') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i> Record Stock In
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Inventory
            </a>
        </div>
    </div>
</div>
@endsection