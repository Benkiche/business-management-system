@extends('layouts.app')

@section('title', 'Customer: ' . $customer->name)
@section('page-title', $customer->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Customer Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Code</small>
                    <strong>{{ $customer->customer_code }}</strong>
                </div>

                @if($customer->email)
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong><a href="mailto:{{ $customer->email }}" class="text-decoration-none">{{ $customer->email }}</a></strong>
                    </div>
                @endif

                @if($customer->phone)
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong><a href="tel:{{ $customer->phone }}" class="text-decoration-none">{{ $customer->phone }}</a></strong>
                    </div>
                @endif

                @if($customer->address)
                    <div class="mb-3">
                        <small class="text-muted d-block">Address</small>
                        <p class="mb-0">{{ $customer->address }}</p>
                        @if($customer->city || $customer->country)
                            <p class="mb-0">
                                @if($customer->city) {{ $customer->city }}, @endif
                                @if($customer->country) {{ $customer->country }} @endif
                                @if($customer->postal_code) {{ $customer->postal_code }} @endif
                            </p>
                        @endif
                    </div>
                @endif

                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning flex-fill">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary flex-fill">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Credit Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Credit Limit</small>
                    <h5 class="mb-0">TZS {{ number_format($customer->credit_limit, 2) }}</h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Outstanding Balance</small>
                    <h5 class="mb-0">
                        <span class="badge {{ $customer->hasExceededCreditLimit() ? 'bg-danger' : 'bg-warning' }}">
                            TZS {{ number_format($customer->outstanding_balance, 2) }}
                        </span>
                    </h5>
                </div>
                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block">Available Credit</small>
                    <h5 class="mb-0 text-success">TZS {{ number_format($customer->available_credit, 2) }}</h5>
                </div>

                @if($customer->hasExceededCreditLimit())
                    <div class="alert alert-danger alert-sm mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i> Exceeded Credit Limit
                    </div>
                @elseif($customer->outstanding_balance > 0)
                    <div class="alert alert-warning alert-sm mt-3 mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i> Outstanding Balance
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab">
                    Sales <span class="badge bg-primary ms-2">{{ $sales->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                    Payments <span class="badge bg-primary ms-2">{{ $payments->total() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="sales" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        @if($sales->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sales as $sale)
                                            <tr>
                                                <td><strong>{{ $sale->invoice_number }}</strong></td>
                                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                                <td>TZS {{ number_format($sale->grand_total, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ 
                                                        $sale->payment_status === 'paid' ? 'bg-success' : 
                                                        ($sale->payment_status === 'partial' ? 'bg-warning' : 'bg-danger')
                                                    }}">
                                                        {{ ucfirst($sale->payment_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $sales->links('pagination::bootstrap-5') }}
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No sales found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="payments" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        @if($payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reference</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $payment)
                                            <tr>
                                                <td><strong>{{ $payment->payment_reference }}</strong></td>
                                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                                <td>TZS {{ number_format($payment->amount, 2) }}</td>
                                                <td>
                                                    <small>{{ ucfirst($payment->payment_method) }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $payments->links('pagination::bootstrap-5') }}
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No payments found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection