@extends('layouts.app')

@section('title', 'Expense Report')
@section('page-title', 'Expense Report')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" action="{{ route('financial.expenses') }}" class="card p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('financial.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-success w-100">
                        <i class="fas fa-download me-2"></i> Export CSV
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Expenses</small>
                <h4 class="mb-0 text-danger">${{ number_format($expenses['total_expenses'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Number of Expenses</small>
                <h4 class="mb-0">{{ number_format($expenses['count']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Average Expense</small>
                <h4 class="mb-0">${{ number_format($expenses['count'] > 0 ? $expenses['total_expenses'] / $expenses['count'] : 0, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Expenses by Category</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses['by_category'] as $expense)
                                <tr>
                                    <td>{{ $expense['category'] }}</td>
                                    <td class="text-end">{{ number_format($expense['count']) }}</td>
                                    <td class="text-end">${{ number_format($expense['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No expenses found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Expenses by Payment Method</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses['by_method'] as $expense)
                                <tr>
                                    <td>{{ ucfirst($expense['method']) }}</td>
                                    <td class="text-end">{{ number_format($expense['count']) }}</td>
                                    <td class="text-end">${{ number_format($expense['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No expenses found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
