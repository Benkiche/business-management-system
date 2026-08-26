@extends('layouts.app')

@section('title', 'Stock Adjustment')
@section('page-title', 'Inventory Adjustment')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Stock Adjustment</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.adjustment') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required onchange="updateProductInfo()">
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-current="{{ $product->quantity_on_hand }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_quantity" class="form-label fw-semibold">Current Quantity</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="current_quantity" 
                                readonly
                            >
                            <small class="text-muted d-block mt-1">System recorded quantity</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="new_quantity" class="form-label fw-semibold">New Quantity <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('new_quantity') is-invalid @enderror" 
                                id="new_quantity" 
                                name="new_quantity" 
                                value="{{ old('new_quantity') }}"
                                min="0"
                                required
                            >
                            @error('new_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Amount</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="adjustment_amount" 
                            readonly
                        >
                        <small class="text-muted d-block mt-1">Difference between new and current quantity</small>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                        <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason" required>
                            <option value="">-- Select Reason --</option>
                            <option value="Physical Count" {{ old('reason') === 'Physical Count' ? 'selected' : '' }}>Physical Count/Reconciliation</option>
                            <option value="System Error" {{ old('reason') === 'System Error' ? 'selected' : '' }}>System Error Correction</option>
                            <option value="Discrepancy Found" {{ old('reason') === 'Discrepancy Found' ? 'selected' : '' }}>Discrepancy Found</option>
                            <option value="Inventory Audit" {{ old('reason') === 'Inventory Audit' ? 'selected' : '' }}>Inventory Audit</option>
                            <option value="Other" {{ old('reason') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea 
                            class="form-control @error('notes') is-invalid @enderror" 
                            id="notes" 
                            name="notes" 
                            rows="3"
                            placeholder="Explanation for this adjustment..."
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info mb-3">
                        <small>
                            <i class="fas fa-info-circle me-2"></i>
                            This will record the adjustment in the inventory history with full audit trail.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check me-2"></i> Apply Adjustment
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
                    Use this form to correct inventory discrepancies found during physical counts or audits.
                </p>
                <hr>
                <h6 class="mb-2">Use cases:</h6>
                <ul class="small text-muted">
                    <li>Physical inventory count results</li>
                    <li>System data correction</li>
                    <li>Inventory audit findings</li>
                    <li>Reconciliation after discrepancies</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
function updateProductInfo() {
    const select = document.getElementById('product_id');
    const option = select.options[select.selectedIndex];
    const current = option.getAttribute('data-current');
    
    document.getElementById('current_quantity').value = current || 0;
    calculateAdjustment();
}

function calculateAdjustment() {
    const current = parseInt(document.getElementById('current_quantity').value) || 0;
    const newQty = parseInt(document.getElementById('new_quantity').value) || 0;
    const adjustment = newQty - current;
    
    const elem = document.getElementById('adjustment_amount');
    elem.value = adjustment;
    
    if (adjustment > 0) {
        elem.className = 'form-control text-success fw-bold';
    } else if (adjustment < 0) {
        elem.className = 'form-control text-danger fw-bold';
    } else {
        elem.className = 'form-control';
    }
}

document.getElementById('new_quantity').addEventListener('input', calculateAdjustment);
</script>
@endsection
@endsection