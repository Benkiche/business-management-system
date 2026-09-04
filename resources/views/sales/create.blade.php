@extends('layouts.app')

@section('title', 'Create Sale')
@section('page-title', 'Create New Sale')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sale Details</h5>
            </div>
            <div class="card-body">
                <form id="saleForm" method="POST" action="{{ route('sales.store') }}" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} (Balance: TZS {{ number_format($customer->outstanding_balance, 2) }})</option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sale_date" class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('sale_date') is-invalid @enderror" id="sale_date" name="sale_date" value="{{ old('sale_date', now()->toDateString()) }}" required>
                            @error('sale_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="mb-3">Sale Items</h6>
                        <div id="itemsContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                    </div>

                    <hr>
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm">
                                <tbody>
                                    <tr><td>Subtotal:</td><td class="text-end fw-bold">$<span id="subtotal">0.00</span></td></tr>
                                    <tr><td>Discount %: <input type="number" name="discount_percent" id="globalDiscount" class="form-control form-control-sm d-inline-block" style="width: 80px;" min="0" max="100" step="0.01" value="0"></td><td class="text-end fw-bold">-$<span id="discountAmount">0.00</span></td></tr>
                                    <tr><td>Tax %: <input type="number" name="tax_percent" id="taxPercent" class="form-control form-control-sm d-inline-block" style="width: 80px;" min="0" max="100" step="0.01" value="0"></td><td class="text-end fw-bold">+$<span id="taxAmount">0.00</span></td></tr>
                                    <tr class="fw-bold"><td>Grand Total:</td><td class="text-end">$<span id="grandTotal">0.00</span></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                @foreach(['cash' => 'Cash', 'credit_card' => 'Credit Card', 'check' => 'Check', 'bank_transfer' => 'Bank Transfer', 'credit_sale' => 'Credit Sale'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6"><label for="due_date" class="form-label fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}"></div>
                    </div>
                    <div class="mb-3"><label for="notes" class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea></div>
                    <div class="d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-check me-2"></i> Create Sale</button><a href="{{ route('sales.index') }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i> Cancel</a></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-3"><div class="card"><div class="card-header bg-light"><h6 class="mb-0">Help</h6></div><div class="card-body small"><p class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i> Create a new sales invoice.</p><ul class="small text-muted"><li>Select customer</li><li>Add products</li><li>Set discounts/tax</li><li>Choose payment method</li><li>Submit to save</li></ul></div></div></div>
</div>
@endsection

@section('js')
<script>
let itemCount = 0;

function addItem() {
    const html = `<div class="item-row mb-3 p-3 border rounded" data-index="${itemCount}"><div class="row g-2"><div class="col-md-4"><label class="form-label">Product</label><select name="items[${itemCount}][product_id]" class="form-select product-select" required onchange="setPrice(this)"><option value="">-- Select Product --</option>@foreach($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">{{ $product->name }} - TZS {{ number_format($product->selling_price, 2) }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label">Qty</label><input type="number" name="items[${itemCount}][quantity]" class="form-control quantity" min="1" value="1" required onchange="calculateTotals()"></div><div class="col-md-2"><label class="form-label">Price</label><input type="number" name="items[${itemCount}][unit_price]" class="form-control unit-price" step="0.01" min="0" required onchange="calculateTotals()"></div><div class="col-md-2"><label class="form-label">Discount %</label><input type="number" name="items[${itemCount}][discount_percent]" class="form-control discount-percent" step="0.01" min="0" max="100" value="0" onchange="calculateTotals()"></div><div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger w-100" onclick="removeItem(this)"><i class="fas fa-trash"></i></button></div></div></div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    itemCount++;
}

function removeItem(button) { button.closest('.item-row').remove(); calculateTotals(); }
function setPrice(select) { select.closest('.item-row').querySelector('.unit-price').value = select.options[select.selectedIndex].getAttribute('data-price'); calculateTotals(); }
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => { const quantity = parseFloat(row.querySelector('.quantity').value) || 0; const price = parseFloat(row.querySelector('.unit-price').value) || 0; const discount = parseFloat(row.querySelector('.discount-percent').value) || 0; subtotal += quantity * price * (1 - discount / 100); });
    const discountAmount = subtotal * ((parseFloat(document.getElementById('globalDiscount').value) || 0) / 100);
    const afterDiscount = subtotal - discountAmount;
    const taxAmount = afterDiscount * ((parseFloat(document.getElementById('taxPercent').value) || 0) / 100);
    document.getElementById('subtotal').textContent = subtotal.toFixed(2); document.getElementById('discountAmount').textContent = discountAmount.toFixed(2); document.getElementById('taxAmount').textContent = taxAmount.toFixed(2); document.getElementById('grandTotal').textContent = (afterDiscount + taxAmount).toFixed(2);
}

document.getElementById('addItemBtn').addEventListener('click', addItem); document.getElementById('globalDiscount').addEventListener('input', calculateTotals); document.getElementById('taxPercent').addEventListener('input', calculateTotals); addItem();
</script>
@endsection