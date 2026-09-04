@extends('layouts.app')

@section('title', 'Expense: ' . $expense->expense_code)
@section('page-title', $expense->expense_code)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Expense Details</h5>
                <span class="badge bg-{{ $expense->status_badge }} text-uppercase">
                    {{ $expense->status }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Expense Code</small>
                        <strong>{{ $expense->expense_code }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ $expense->expense_date ? $expense->expense_date->format('M d, Y') : 'N/A' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Category</small>
                        <strong>{{ $expense->category->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Recorded By</small>
                        <strong>{{ $expense->recordedBy->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Amount</small>
                        <h5 class="mb-0 text-primary">TZS {{ number_format($expense->amount, 2) }}</h5>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Payment Method</small>
                        <strong>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</strong>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted d-block">Description</small>
                    <p class="mb-0">{{ $expense->description }}</p>
                </div>

                @if($expense->notes)
                    <div class="mb-3">
                        <small class="text-muted d-block">Notes</small>
                        <p class="mb-0">{{ $expense->notes }}</p>
                    </div>
                @endif

                @if($expense->receipt_path)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2">Receipt / Attachment</small>
                        <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-download me-2"></i> View Receipt
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->hasPermission('expenses.edit'))
                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i> Edit Expense
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('expenses.delete'))
                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i> Delete Expense
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
