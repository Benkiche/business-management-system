@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers Management')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Customers List</h5>
        @if(auth()->user()->hasPermission('customers.create'))
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> New Customer
            </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Search by name, email, or phone..."
                        value="{{ request('search') }}"
                    >
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="debt_status" class="form-select">
                        <option value="">All Debts</option>
                        <option value="with_debt" {{ request('debt_status') == 'with_debt' ? 'selected' : '' }}>With Outstanding Balance</option>
                        <option value="exceeded_limit" {{ request('debt_status') == 'exceeded_limit' ? 'selected' : '' }}>Exceeded Credit Limit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Credit Limit</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td><strong>{{ $customer->name }}</strong></td>
                                <td><small class="text-muted">{{ $customer->customer_code }}</small></td>
                                <td>
                                    @if($customer->phone)
                                        <small class="d-block">{{ $customer->phone }}</small>
                                    @endif
                                    @if($customer->email)
                                        <small class="text-muted">{{ $customer->email }}</small>
                                    @endif
                                </td>
                                <td>TZS {{ number_format($customer->credit_limit, 2) }}</td>
                                <td>
                                    <span class="badge {{ $customer->hasExceededCreditLimit() ? 'bg-danger' : 'bg-warning' }}">
                                        TZS {{ number_format($customer->outstanding_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('customers.edit'))
                                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('customers.delete'))
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Delete"
                                                onclick="confirmDelete('{{ route('customers.destroy', $customer) }}', '{{ $customer->name }}')"
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

            {{ $customers->links('pagination::bootstrap-5') }}
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No customers found.</p>
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
                <p>Are you sure you want to delete customer <strong id="deleteName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
function confirmDelete(url, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = url;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
@endsection