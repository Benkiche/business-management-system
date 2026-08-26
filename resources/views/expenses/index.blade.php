@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses Management')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Expenses List</h5>
        @if(auth()->user()->hasPermission('expenses.create'))
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> New Expense
            </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Search code or description..."
                        value="{{ request('search') }}"
                    >
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        @if($expenses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            <tr>
                                <td><small class="text-muted">{{ $expense->expense_code }}</small></td>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td>{{ $expense->category->name }}</td>
                                <td>{{ Str::limit($expense->description, 30) }}</td>
                                <td class="text-end fw-bold">${{ number_format($expense->amount, 2) }}</td>
                                <td><small>{{ ucfirst($expense->payment_method) }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $expense->status_badge }}">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </td>
                                <td><small>{{ $expense->recordedBy->name }}</small></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('expenses.edit'))
                                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('expenses.delete'))
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Delete"
                                                onclick="confirmDelete('{{ route('expenses.destroy', $expense) }}', '{{ $expense->expense_code }}')"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $expenses->links('pagination::bootstrap-5') }}
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No expenses found.</p>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete expense <strong id="deleteName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
function confirmDelete(url, code) {
    document.getElementById('deleteName').textContent = code;
    document.getElementById('deleteForm').action = url;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
@endsection