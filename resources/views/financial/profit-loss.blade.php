@extends('layouts.app')

@section('title', 'Profit & Loss Statement')
@section('page-title', 'Profit & Loss Statement')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" action="{{ route('financial.profit-loss') }}" class="card p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Generate</button>
                    <a href="{{ route('financial.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-success" title="Export CSV">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h4 class="mb-1">INCOME STATEMENT</h4>
                <small class="text-muted">{{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</small>
            </div>
        </div>

        <table class="table">
            <tbody>
                <tr>
                    <td class="fw-bold">Revenue</td>
                    <td class="text-end fw-bold">${{ number_format($pnl['revenue'], 2) }}</td>
                </tr>
                <tr>
                    <td>Less: Cost of Goods Sold</td>
                    <td class="text-end">${{ number_format($pnl['cost_of_goods_sold'], 2) }}</td>
                </tr>
                <tr class="border-bottom fw-bold">
                    <td>GROSS PROFIT</td>
                    <td class="text-end text-success">${{ number_format($pnl['gross_profit'], 2) }} ({{ number_format($pnl['gross_margin'], 1) }}%)</td>
                </tr>

                <tr class="table-light">
                    <td class="fw-bold">Operating Expenses</td>
                    <td></td>
                </tr>

                @foreach($pnl['expenses_breakdown'] as $expense)
                    <tr>
                        <td style="padding-left: 40px;">{{ $expense['category'] }}</td>
                        <td class="text-end">${{ number_format($expense['total'], 2) }}</td>
                    </tr>
                @endforeach

                <tr class="border-bottom">
                    <td style="padding-left: 40px;" class="fw-bold">Total Operating Expenses</td>
                    <td class="text-end fw-bold">${{ number_format($pnl['operating_expenses'], 2) }}</td>
                </tr>

                <tr class="fw-bold text-lg border-top border-bottom">
                    <td>NET PROFIT / LOSS</td>
                    <td class="text-end {{ $pnl['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        ${{ number_format($pnl['net_profit'], 2) }} ({{ number_format($pnl['net_margin'], 1) }}%)
                    </td>
                </tr>

                <tr class="table-light">
                    <td class="fw-bold">Summary</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Discounts Given</td>
                    <td class="text-end">${{ number_format($pnl['total_discount_given'], 2) }}</td>
                </tr>
                <tr>
                    <td>Tax Collected</td>
                    <td class="text-end">${{ number_format($pnl['total_tax_collected'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection