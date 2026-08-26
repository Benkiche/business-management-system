@extends('layouts.app')

@section('title', 'Record Payment')
@section('page-title', 'Record Payment')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('payments.store', $sale) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Invoice</label>
                        <input type="text" class="form-control" value="{{ $sale->invoice_number }}" disabled>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer</label>
                            <input type="text" class="form-control" value="{{ $sale->customer->name }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="text" class="form-control" value="{{ $sale->sale_date->format('M d, Y') }}" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Invoice Total</label>
                            <input type="text" class="form-control" value="${{ number_format($sale->grand_total, 2) }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Outstanding</label>
                            <input type="text" class="form-control" value="${{ number_format($sale->outstanding_balance, 2) }}" disabled>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" step="0.01" min="0.01" 
                                       max="{{ $sale->outstanding_balance }}"
                                       value="{{ old('amount', $sale->outstanding_balance) }}" required>
                            </div>
                            <small class="text-muted d-block mt-1">Max: ${{ number_format($sale->outstanding_balance, 2) }}</small>
                            @error('amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="payment_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   id="payment_date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label fw-semibold">Method <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" 
                                id="payment_method" name="payment_method" required>
                            <option value="">-- Select --</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i> Record Payment
                        </button>
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Invoice Total</small>
                    <h5>${{ number_format($sale->grand_total, 2) }}</h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Already Paid</small>
                    <h5>${{ number_format($sale->amount_paid, 2) }}</h5>
                </div>
                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block">Remaining</small>
                    <h4>${{ number_format($sale->outstanding_balance, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection