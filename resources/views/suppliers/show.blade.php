@extends('layouts.app')

@section('title', 'Invoice: ' . $sale->invoice_number)
@section('page-title', 'Invoice: ' . $sale->invoice_number)

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $sale->invoice_number }}</h5>
            <small class="text-muted">{{ $sale->sale_date->format('M d, Y') }}</small>
        </div>
        <div class="btn-group">
            @if(!$sale->isPaid() && $sale->status === 'completed')
                <a href="{{ route('payments.create', $sale) }}" class="btn btn-success">
                    <i class="fas fa-money-bill me-2"></i> Record Payment
                </a>
            @endif
            @if($sale->status === 'completed' && $sale->outstanding_balance == $sale->grand_total)
                <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this sale?')">
                        <i class="fas fa-times me-2"></i> Cancel
                    </button>
                </form>
            @endif
            <button class="btn btn-secondary" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Invoice Card -->
        <div class="card">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="row mb-5">
                    <div class="col-md-6">
                        <h4>INVOICE</h4>
                        <p class="text-muted mb-0">{{ $sale->invoice_number }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6>Business Name</h6>
                        <small class="text-muted">Address Line</small>
                    </div>
                </div>

                <!-- Billing -->
                <div class="row mb-5">
                    <div class="col-md-6">
                        <h6 class="mb-2">Bill To</h6>
                        <strong>{{ $sale->customer->name }}</strong>
                        @if($sale->customer->email)
                            <br><small class="text-muted">{{ $sale->customer->email }}</small>
                        @endif
                        @if($sale->customer->phone)
                            <br><small class="text-muted">{{ $sale->customer->phone }}</small>
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="mb-2">
                            <small class="text-muted d-block">Invoice Date</small>
                            <strong>{{ $sale->sale_date->format('M d, Y') }}</strong>
                        </div>
                        @if($sale->due_date)
                            <div>
                                <small class="text-muted d-block">Due Date</small>
                                <strong>{{ $sale->due_date->format('M d, Y') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table">
                        <thead class="border-top border-bottom">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        <br><small class="text-muted">{{ $item->product->product_code }}</small>
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">TZS {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">
                                        @if($item->discount_percent > 0)
                                            {{ $item->discount_percent }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">TZS {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row justify-content-end mb-4">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($sale->subtotal, 2) }}</td>
                                </tr>
                                @if($sale->discount_amount > 0)
                                    <tr class="text-danger">
                                        <td>Discount</td>
                                        <td class="text-end">-TZS {{ number_format($sale->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                @if($sale->tax_amount > 0)
                                    <tr>
                                        <td>Tax</td>
                                        <td class="text-end">+TZS {{ number_format($sale->tax_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="fw-bold">TOTAL</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($sale->grand_total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="border-top pt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Payment Method</small>
                            <strong>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</strong>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'partial' ? 'info' : 'warning') }}">
                                {{ ucfirst($sale->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($sale->notes)
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Notes</h6>
                        <p class="mb-0 small">{{ $sale->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Payment Status -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Payment Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Amount Due</small>
                    <h5 class="mb-0">TZS {{ number_format($sale->grand_total, 2) }}</h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Amount Paid</small>
                    <h5 class="mb-0">TZS {{ number_format($sale->amount_paid, 2) }}</h5>
                </div>
                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block">Outstanding</small>
                    <h4 class="mb-0">
                        TZS {{ number_format($sale->outstanding_balance, 2) }}
                        <span class="badge {{ $sale->isPaid() ? 'bg-success' : 'bg-warning' }} ms-2">
                            {{ $sale->isPaid() ? 'PAID' : 'DUE' }}
                        </span>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Payments -->
        @if($sale->payments->count() > 0)
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Payments Received</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($sale->payments as $payment)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small class="text-muted">{{ $payment->payment_date->format('M d, Y') }}</small>
                                        <div class="fw-bold">TZS {{ number_format($payment->amount, 2) }}</div>
                                    </div>
                                    <small class="text-muted">{{ ucfirst($payment->payment_method) }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Info -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Sale Information</h6>
            </div>
            <div class="card-body small">
                <div class="mb-2">
                    <small class="text-muted d-block">Salesperson</small>
                    <strong>{{ $sale->salesperson->name }}</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Items</small>
                    <strong>{{ $sale->items->count() }} products</strong>
                </div>
                <div>
                    <small class="text-muted d-block">Created</small>
                    <strong>{{ $sale->created_at->format('M d, Y H:i') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

@section('css')
<style>
    @media print {
        .btn-group, .btn { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
    }
</style>
@endsection
@endsection