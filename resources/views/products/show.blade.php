@extends('layouts.app')

@section('title', 'Product: ' . $product->name)
@section('page-title', $product->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        @if($product->product_image_path)
                            <img 
                                src="{{ route('products.image', $product) }}"
                                alt="{{ $product->name }}"
                                class="img-fluid rounded"
                                style="max-height: 300px;"
                            >
                        @else
                            <div class="bg-light rounded p-5">
                                <i class="fas fa-image fa-5x text-muted"></i>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-8">
                        <h6 class="text-muted mb-1">Product Code</h6>
                        <h5 class="mb-3">{{ $product->product_code }}</h5>

                        @if($product->sku)
                            <div class="mb-3">
                                <small class="text-muted d-block">SKU</small>
                                <strong>{{ $product->sku }}</strong>
                            </div>
                        @endif

                        @if($product->barcode)
                            <div class="mb-3">
                                <small class="text-muted d-block">Barcode</small>
                                <strong>{{ $product->barcode }}</strong>
                            </div>
                        @endif

                        <div class="mb-3">
                            <small class="text-muted d-block">Category</small>
                            <strong>
                                <a href="{{ route('categories.show', $product->category) }}" class="text-decoration-none">
                                    {{ $product->category->name }}
                                </a>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Supplier</small>
                            <strong>
                                <a href="{{ route('suppliers.show', $product->supplier) }}" class="text-decoration-none">
                                    {{ $product->supplier->name }}
                                </a>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($product->description)
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Description</h6>
                        <p>{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Pricing</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Purchase Price</small>
                    <h5 class="mb-0">TZS {{ number_format($product->purchase_price, 2) }}</h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Selling Price</small>
                    <h5 class="mb-0">TZS {{ number_format($product->selling_price, 2) }}</h5>
                </div>
                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block">Profit Margin</small>
                    <h5 class="mb-0 text-success">{{ number_format($product->profit_margin, 2) }}%</h5>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Inventory</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Quantity on Hand</small>
                    <h5 class="mb-0">
                        <span class="badge {{ 
                            $product->quantity_on_hand === 0 ? 'bg-danger' : 
                            ($product->quantity_on_hand <= $product->minimum_stock_level ? 'bg-warning' : 'bg-success')
                        }}">
                            {{ $product->quantity_on_hand }} units
                        </span>
                    </h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Minimum Stock Level</small>
                    <p class="mb-0">{{ $product->minimum_stock_level }} units</p>
                </div>

                @if($product->isOutOfStock())
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i> Out of Stock
                    </div>
                @elseif($product->isLowStock())
                    <div class="alert alert-warning alert-sm mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i> Low Stock
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('products.edit'))
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning flex-fill">
                    <i class="fas fa-edit me-2"></i> Edit
                </a>
            @endif
            <a href="{{ route('products.index') }}" class="btn btn-secondary flex-fill">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>
</div>
@endsection