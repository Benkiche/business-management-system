@extends('layouts.app')

@section('title', 'Compare Financial Periods')
@section('page-title', 'Compare Financial Periods')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" action="{{ route('financial.compare') }}" class="card p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="current_from" class="form-label">Current From</label>
                    <input type="date" id="current_from" name="current_from" class="form-control" value="{{ $currentFromDate }}">
                </div>
                <div class="col-md-3">
                    <label for="current_to" class="form-label">Current To</label>
                    <input type="date" id="current_to" name="current_to" class="form-control" value="{{ $currentToDate }}">
                </div>
                <div class="col-md-3">
                    <label for="previous_from" class="form-label">Previous From</label>
                    <input type="date" id="previous_from" name="previous_from" class="form-control" value="{{ $previousFromDate }}">
                </div>
                <div class="col-md-3">
                    <label for="previous_to" class="form-label">Previous To</label>
                    <input type="date" id="previous_to" name="previous_to" class="form-control" value="{{ $previousToDate }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Compare Periods</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h6 class="mb-0">Financial Comparison</h6>
            </div>
            <div class="col-md-4 text-md-center text-muted small">
                {{ \Carbon\Carbon::parse($currentFromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($currentToDate)->format('M d, Y') }}
            </div>
            <div class="col-md-4 text-md-end text-muted small">
                {{ \Carbon\Carbon::parse($previousFromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($previousToDate)->format('M d, Y') }}
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Metric</th>
                        <th class="text-end">Current Period</th>
                        <th class="text-end">Previous Period</th>
                        <th class="text-end">Change</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Revenue</td>
                        <td class="text-end">${{ number_format($comparison['current_period']['revenue'], 2) }}</td>
                        <td class="text-end">${{ number_format($comparison['previous_period']['revenue'], 2) }}</td>
                        <td class="text-end {{ $comparison['changes']['revenue_change_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $comparison['changes']['revenue_change_percent'] >= 0 ? '+' : '' }}{{ number_format($comparison['changes']['revenue_change_percent'], 1) }}%
                        </td>
                    </tr>
                    <tr>
                        <td>Cost of Goods Sold</td>
                        <td class="text-end">${{ number_format($comparison['current_period']['cost_of_goods_sold'], 2) }}</td>
                        <td class="text-end">${{ number_format($comparison['previous_period']['cost_of_goods_sold'], 2) }}</td>
                        <td class="text-end text-muted">&mdash;</td>
                    </tr>
                    <tr>
                        <td>Gross Profit</td>
                        <td class="text-end">${{ number_format($comparison['current_period']['gross_profit'], 2) }}</td>
                        <td class="text-end">${{ number_format($comparison['previous_period']['gross_profit'], 2) }}</td>
                        <td class="text-end text-muted">&mdash;</td>
                    </tr>
                    <tr>
                        <td>Operating Expenses</td>
                        <td class="text-end">${{ number_format($comparison['current_period']['operating_expenses'], 2) }}</td>
                        <td class="text-end">${{ number_format($comparison['previous_period']['operating_expenses'], 2) }}</td>
                        <td class="text-end {{ $comparison['changes']['expense_change_percent'] <= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $comparison['changes']['expense_change_percent'] >= 0 ? '+' : '' }}{{ number_format($comparison['changes']['expense_change_percent'], 1) }}%
                        </td>
                    </tr>
                    <tr class="border-top fw-bold">
                        <td>Net Profit / Loss</td>
                        <td class="text-end {{ $comparison['current_period']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($comparison['current_period']['net_profit'], 2) }}</td>
                        <td class="text-end {{ $comparison['previous_period']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($comparison['previous_period']['net_profit'], 2) }}</td>
                        <td class="text-end {{ $comparison['changes']['profit_change_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $comparison['changes']['profit_change_percent'] >= 0 ? '+' : '' }}{{ number_format($comparison['changes']['profit_change_percent'], 1) }}%
                        </td>
                    </tr>
                    <tr>
                        <td>Gross Margin</td>
                        <td class="text-end">{{ number_format($comparison['current_period']['gross_margin'], 1) }}%</td>
                        <td class="text-end">{{ number_format($comparison['previous_period']['gross_margin'], 1) }}%</td>
                        <td class="text-end text-muted">&mdash;</td>
                    </tr>
                    <tr>
                        <td>Net Margin</td>
                        <td class="text-end">{{ number_format($comparison['current_period']['net_margin'], 1) }}%</td>
                        <td class="text-end">{{ number_format($comparison['previous_period']['net_margin'], 1) }}%</td>
                        <td class="text-end text-muted">&mdash;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
