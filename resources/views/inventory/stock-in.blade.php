@extends('layouts.app')

@section('title', 'Stock In')
@section('page-title', 'Record Stock In')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Stock In Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.stock-in') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (Stock: {{ $product->quantity_on_hand }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('quantity') is-invalid @enderror" 
                                id="quantity" 
                                name="quantity" 
                                value="{{ old('quantity') }}"
                                min="1"
                                required
                            >
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit_cost" class="form-label fw-semibold">Unit Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control @error('unit_cost') is-invalid @enderror" 
                                    id="unit_cost" 
                                    name="unit_cost" 
                                    value="{{ old('unit_cost') }}"
                                    step="0.01"
                                    min="0"
                                >
                            </div>
                            <small class="text-muted d-block mt-1">Leave blank to use product's default purchase price</small>
                            @error('unit_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea 
                            class="form-control @error('notes') is-invalid @enderror" 
                            id="notes" 
                            name="notes" 
                            rows="3"
                            placeholder="e.g., Purchase Order #, Invoice details, etc."
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i> Record Stock In
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Information</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Use this form to record goods received from suppliers or transfers from other locations.
                </p>
                <hr>
                <h6 class="mb-2">What this does:</h6>
                <ul class="small text-muted">
                    <li>Increases product quantity</li>
                    <li>Records unit cost for valuation</li>
                    <li>Creates audit trail</li>
                    <li>Updates stock levels</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection