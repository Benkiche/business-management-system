@extends('layouts.app')

@section('title', 'Create Expense')
@section('page-title', 'Record New Expense')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Expense Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expense_category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('expense_category_id') is-invalid @enderror" id="expense_category_id" name="expense_category_id" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('expense_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="expense_date" class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                class="form-control @error('expense_date') is-invalid @enderror" 
                                id="expense_date" 
                                name="expense_date" 
                                value="{{ old('expense_date', now()->toDateString()) }}"
                                required
                            >
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3"
                            required
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control @error('amount') is-invalid @enderror" 
                                    id="amount" 
                                    name="amount" 
                                    value="{{ old('amount') }}"
                                    step="0.01"
                                    min="0.01"
                                    required
                                >
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                <option value="">-- Select --</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="receipt" class="form-label fw-semibold">Receipt/Document</label>
                        <input 
                            type="file" 
                            class="form-control @error('receipt') is-invalid @enderror" 
                            id="receipt" 
                            name="receipt"
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                        <small class="text-muted d-block mt-1">PDF or image file (max 5MB)</small>
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea 
                            class="form-control" 
                            id="notes" 
                            name="notes" 
                            rows="3"
                        >{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Record Expense
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
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
                <p class="text-muted mb-3 small">
                    <i class="fas fa-info-circle me-2"></i>
                    Use this form to record business expenses including receipts.
                </p>
                <ul class="small text-muted">
                    <li>Select appropriate expense category</li>
                    <li>Provide clear description</li>
                    <li>Attach receipt/proof of payment</li>
                    <li>Set status (pending = requires approval)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection