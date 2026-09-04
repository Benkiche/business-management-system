@extends('layouts.app')

@section('title', 'Inventory History: ' . $product->name)
@section('page-title', 'Inventory History: ' . $product->name)

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Product</h6>
                <h5 class="mb-2">{{ $product->name }}</h5>
                <small class="text-muted d-block">{{ $product->product_code }}</small>
                <small class="text-muted">SKU: {{ $product->sku ?? 'N/A' }}</small>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Stock Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Current Stock</small>
                    <h5 class="mb-0">{{ $product->quantity_on_hand }} units</h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Minimum Level</small>
                    <p class="mb-0">{{ $product->minimum_stock_level }} units</p>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Total Movements</small>
                    <p class="mb-0">{{ $summary['movement_count'] }}</p>
                </div>
                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block">Inventory Value</small>
                    <h6 class="mb-0">TZS {{ number_format($summary['inventory_value'], 2) }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Movement History</h6>
                <span class="badge bg-primary">{{ $movements->total() }}</span>
            </div>
            <div class="card-body">
                @if($movements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th>Reference</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $movement)
                                    <tr>
                                        <td>
                                            <small>{{ $movement->movement_date->format('M d, Y H:i') }}</small>
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
                                        <td class="text-end">
                                            @if($movement->unit_cost)
                                                TZS {{ number_format($movement->unit_cost, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{ $movement->reference_type ?? 'Manual' }}
                                                @if($movement->reference_id)
                                                    #{{ $movement->reference_id }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{ $movement->creator->name }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $movements->links('pagination::bootstrap-5') }}
                @else
                    <p class="text-muted text-center py-4">No movements recorded</p>
                @endif
            </div>
        </div>

        @if($product->description)
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Description</h6>
                </div>
                <div class="card-body">
                    {{ $product->description }}
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Inventory
    </a>
</div>
@endsection