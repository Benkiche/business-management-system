@extends('layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product: ' . $product->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', $product->name) }}" 
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label fw-semibold">SKU</label>
                            <input 
                                type="text" 
                                class="form-control @error('sku') is-invalid @enderror" 
                                id="sku" 
                                name="sku" 
                                value="{{ old('sku', $product->sku) }}"
                            >
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label fw-semibold">Barcode</label>
                            <input 
                                type="text" 
                                class="form-control @error('barcode') is-invalid @enderror" 
                                id="barcode" 
                                name="barcode" 
                                value="{{ old('barcode', $product->barcode) }}"
                            >
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                <option value="">-- Select Supplier --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="product_image" class="form-label fw-semibold">Product Image</label>
                            <input 
                                type="file" 
                                class="form-control @error('product_image') is-invalid @enderror" 
                                id="product_image" 
                                name="product_image"
                                accept="image/*"
                            >
                            <small class="text-muted">Leave blank to keep current image</small>
                            @error('product_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="purchase_price" class="form-label fw-semibold">Purchase Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control @error('purchase_price') is-invalid @enderror" 
                                    id="purchase_price" 
                                    name="purchase_price" 
                                    value="{{ old('purchase_price', $product->purchase_price) }}"
                                    step="0.01"
                                    min="0"
                                    required
                                >
                            </div>
                            @error('purchase_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="selling_price" class="form-label fw-semibold">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control @error('selling_price') is-invalid @enderror" 
                                    id="selling_price" 
                                    name="selling_price" 
                                    value="{{ old('selling_price', $product->selling_price) }}"
                                    step="0.01"
                                    min="0"
                                    required
                                >
                            </div>
                            @error('selling_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity_on_hand" class="form-label fw-semibold">Quantity on Hand <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('quantity_on_hand') is-invalid @enderror" 
                                id="quantity_on_hand" 
                                name="quantity_on_hand" 
                                value="{{ old('quantity_on_hand', $product->quantity_on_hand) }}"
                                min="0"
                                required
                            >
                            @error('quantity_on_hand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="minimum_stock_level" class="form-label fw-semibold">Minimum Stock Level <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('minimum_stock_level') is-invalid @enderror" 
                                id="minimum_stock_level" 
                                name="minimum_stock_level" 
                                value="{{ old('minimum_stock_level', $product->minimum_stock_level) }}"
                                min="0"
                                required
                            >
                            @error('minimum_stock_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea 
                            class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="4"
                        >{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Product
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Product Details</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Code</small>
                    <strong>{{ $product->product_code }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Profit Margin</small>
                    <strong>{{ number_format($product->profit_margin, 2) }}%</strong>
                </div>
                <hr>
                @if($product->product_image_path)
                    <div class="mb-3">
                        <img 
                            src="{{ asset('storage/' . $product->product_image_path) }}" 
                            alt="{{ $product->name }}"
                            class="img-fluid rounded"
                        >
                    </div>
                @else
                    <div class="bg-light rounded p-4 text-center mb-3">
                        <i class="fas fa-image fa-3x text-muted"></i>
                        <p class="text-muted mt-2">No image</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection