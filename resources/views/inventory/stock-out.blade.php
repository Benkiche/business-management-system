@extends('layouts.app')

@section('title', 'Stock Out')
@section('page-title', 'Record Stock Out')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Stock Out Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.stock-out') }}" novalidate>
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
                            <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                            <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason" required>
                                <option value="">-- Select Reason --</option>
                                <option value="Damage" {{ old('reason') === 'Damage' ? 'selected' : '' }}>Damage</option>
                                <option value="Expiry" {{ old('reason') === 'Expiry' ? 'selected' : '' }}>Expiry/Spoilage</option>
                                <option value="Loss" {{ old('reason') === 'Loss' ? 'selected' : '' }}>Loss</option>
                                <option value="Transfer" {{ old('reason') === 'Transfer' ? 'selected' : '' }}>Transfer to Another Branch</option>
                                <option value="Shrinkage" {{ old('reason') === 'Shrinkage' ? 'selected' : '' }}>Shrinkage</option>
                                <option value="Other" {{ old('reason') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('reason')
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
                            placeholder="Additional details about this stock out..."
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check me-2"></i> Record Stock Out
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
                    Use this form to reduce inventory due to damage, expiry, loss, or other reasons.
                </p>
                <div class="alert alert-warning alert-sm mb-3">
                    <strong>Note:</strong> This does NOT record a sale. Use the Sales module for customer purchases.
                </div>
                <hr>
                <h6 class="mb-2">What this does:</h6>
                <ul class="small text-muted">
                    <li>Decreases product quantity</li>
                    <li>Records reason for tracking</li>
                    <li>Creates audit trail</li>
                    <li>Updates stock levels</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection