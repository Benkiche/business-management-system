@extends('layouts.app')

@section('title', 'Balance Sheet')
@section('page-title', 'Balance Sheet')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Statement of Financial Position</h5>
                    <small class="text-muted">Current balances</small>
                </div>
                <i class="fas fa-scale-balanced fa-2x text-primary"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Assets</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <td>Inventory</td>
                            <td class="text-end">TZS {{ number_format($balanceSheet['assets']['inventory'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Customer Receivables</td>
                            <td class="text-end">TZS {{ number_format($balanceSheet['assets']['customer_receivables'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Cash</td>
                            <td class="text-end">TZS {{ number_format($balanceSheet['assets']['cash'], 2) }}</td>
                        </tr>
                        <tr class="border-top fw-bold">
                            <td>Total Assets</td>
                            <td class="text-end text-success">TZS {{ number_format($balanceSheet['assets']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">Liabilities and Equity</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <td>Supplier Payables</td>
                            <td class="text-end">TZS {{ number_format($balanceSheet['liabilities']['supplier_payables'], 2) }}</td>
                        </tr>
                        <tr class="border-top fw-bold">
                            <td>Total Liabilities</td>
                            <td class="text-end text-danger">TZS {{ number_format($balanceSheet['liabilities']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Retained Equity</td>
                            <td class="text-end">TZS {{ number_format($balanceSheet['equity'], 2) }}</td>
                        </tr>
                        <tr class="border-top fw-bold">
                            <td>Total Liabilities and Equity</td>
                            <td class="text-end">TZS {{ number_format($balanceSheet['liabilities']['total'] + $balanceSheet['equity'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="alert {{ $balanceSheet['assets']['total'] == $balanceSheet['liabilities']['total'] + $balanceSheet['equity'] ? 'alert-success' : 'alert-warning' }} mb-0">
            <i class="fas fa-{{ $balanceSheet['assets']['total'] == $balanceSheet['liabilities']['total'] + $balanceSheet['equity'] ? 'check' : 'triangle-exclamation' }} me-2"></i>
            {{ $balanceSheet['assets']['total'] == $balanceSheet['liabilities']['total'] + $balanceSheet['equity'] ? 'Balance sheet is balanced.' : 'Balance sheet requires review.' }}
        </div>
    </div>
</div>
@endsection
