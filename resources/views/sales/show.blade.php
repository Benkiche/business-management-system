@extends('layouts.app')

@section('title', 'Sale ' . $sale->invoice_number)
@section('page-title', 'Sale Details')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">{{ $sale->invoice_number }}</h4>
        <div class="text-muted">Created {{ $sale->created_at?->format('M d, Y H:i') }}</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Sales</a>
        @if(!$sale->isCancelled())
            @if(!$sale->isPaid())
                <a href="{{ route('payments.create', $sale) }}" class="btn btn-success"><i class="fas fa-credit-card me-1"></i> Record Payment</a>
            @endif
            @if((float) $sale->amount_paid === 0.0)
                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-primary"><i class="fas fa-edit me-1"></i> Edit</a>
            @endif
            <form method="POST" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('Cancel this sale? Inventory will be restored.');">
                @csrf
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-ban me-1"></i> Cancel Sale</button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Items</h5>
                <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : ($sale->status === 'cancelled' ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst($sale->status) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name ?? 'Product unavailable' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">TZS {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->discount, 2) }}%</td>
                                    <td class="text-end">TZS {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No items recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Payments</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Date</th><th>Method</th><th>Recorded By</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($sale->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('M d, Y') }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td>{{ $payment->recordedBy?->name ?? 'Unknown' }}</td>
                                    <td class="text-end">TZS {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Sale Summary</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>TZS {{ number_format($sale->subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong>-TZS {{ number_format($sale->discount_amount, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-3"><span>Tax</span><strong>TZS {{ number_format($sale->tax_amount, 2) }}</strong></div>
                <hr>
                <div class="d-flex justify-content-between mb-2"><span class="fw-semibold">Grand Total</span><strong>TZS {{ number_format($sale->grand_total, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Paid</span><span class="text-success">TZS {{ number_format($sale->amount_paid, 2) }}</span></div>
                <div class="d-flex justify-content-between"><span>Outstanding</span><strong class="{{ $sale->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">TZS {{ number_format($sale->outstanding_balance, 2) }}</strong></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Customer & Sale Info</h5></div>
            <div class="card-body">
                <div class="mb-3"><small class="text-muted d-block">Customer</small><strong>{{ $sale->customer?->name ?? 'Unknown' }}</strong></div>
                <div class="mb-3"><small class="text-muted d-block">Salesperson</small>{{ $sale->salesperson?->name ?? 'Unknown' }}</div>
                <div class="mb-3"><small class="text-muted d-block">Sale Date</small>{{ $sale->sale_date?->format('M d, Y') }}</div>
                <div class="mb-3"><small class="text-muted d-block">Due Date</small>{{ $sale->due_date?->format('M d, Y') ?? 'Not set' }}</div>
                <div><small class="text-muted d-block">Payment Method</small>{{ ucwords(str_replace('_', ' ', $sale->payment_method)) }}</div>
            </div>
        </div>

        @if($sale->notes)
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Notes</h5></div>
                <div class="card-body">{!! nl2br(e($sale->notes)) !!}</div>
            </div>
        @endif
    </div>
</div>
@endsection
