@extends('layouts.app')

@section('title', 'Inventory Valuation Report')
@section('page-title', 'Inventory Valuation Report')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventory Valuation Summary</h5>
        <a href="{{ route('inventory.valuation-export-csv') }}" class="btn btn-success">
            <i class="fas fa-download me-2"></i> Export CSV
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Cost Value</small>
                <h4 class="mb-0">TZS {{ number_format($valuation['total_cost_value'], 2) }}</h4>
                <small class="text-success">at purchase price</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Total Retail Value</small>
                <h4 class="mb-0">TZS {{ number_format($valuation['total_retail_value'], 2) }}</h4>
                <small class="text-info">at selling price</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block">Profit Potential</small>
                <h4 class="mb-0">TZS {{ number_format($valuation['total_profit'], 2) }}</h4>
                <small class="text-success">Gross profit if sold</small>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Cost/Unit</th>
                        <th class="text-end">Cost Value</th>
                        <th class="text-end">Price/Unit</th>
                        <th class="text-end">Retail Value</th>
                        <th class="text-end">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($valuation['items'] as $item)
                        <tr>
                            <td><small class="text-muted">{{ $item['product_code'] }}</small></td>
                            <td><strong>{{ $item['product_name'] }}</strong></td>
                            <td class="text-end">{{ $item['quantity'] }}</td>
                            <td class="text-end">TZS {{ number_format($item['unit_cost'], 2) }}</td>
                            <td class="text-end">TZS {{ number_format($item['cost_value'], 2) }}</td>
                            <td class="text-end">TZS {{ number_format($item['unit_price'], 2) }}</td>
                            <td class="text-end">TZS {{ number_format($item['retail_value'], 2) }}</td>
                            <td class="text-end">
                                <span class="badge bg-success">
                                    {{ number_format($item['profit_margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">TOTALS:</td>
                        <td class="text-end">TZS {{ number_format($valuation['total_cost_value'], 2) }}</td>
                        <td></td>
                        <td class="text-end">TZS {{ number_format($valuation['total_retail_value'], 2) }}</td>
                        <td class="text-end">
                            <span class="badge bg-success">
                                {{ $valuation['total_cost_value'] > 0 ? number_format((($valuation['total_profit'] / $valuation['total_cost_value']) * 100), 1) : 0 }}%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Inventory
    </a>
</div>
@endsection