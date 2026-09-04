@extends('layouts.app')

@section('title', 'Category: ' . $category->name)
@section('page-title', $category->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title text-muted">Category Details</h6>
                <div class="mb-3">
                    <small class="text-muted">Name</small>
                    <p class="fw-bold">{{ $category->name }}</p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Status</small>
                    <p>
                        <span class="badge {{ $category->status === 'active' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($category->status) }}
                        </span>
                    </p>
                </div>
                @if($category->description)
                    <div class="mb-3">
                        <small class="text-muted">Description</small>
                        <p>{{ $category->description }}</p>
                    </div>
                @endif
                <div class="mb-3">
                    <small class="text-muted">Created</small>
                    <p>{{ $category->created_at->format('M d, Y H:i') }}</p>
                </div>

                @if(auth()->user()->hasPermission('products.edit'))
                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Products in this Category</h5>
            </div>
            <div class="card-body">
                @if($products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                        </td>
                                        <td>{{ $product->sku ?? '-' }}</td>
                                        <td>TZS {{ number_format($product->selling_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $product->quantity_on_hand > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $product->quantity_on_hand }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $products->links('pagination::bootstrap-5') }}
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No products in this category.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection