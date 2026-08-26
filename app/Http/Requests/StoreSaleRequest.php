<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermission('sales.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'exists:customers,id',
                'exists:customers,id,status,active', // Only active customers
            ],
            'payment_method' => [
                'required',
                'in:cash,credit_card,check,bank_transfer,credit_sale',
            ],
            'discount_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'tax_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sale_date' => ['required', 'date'],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:50', // Limit items per sale
            ],
            'items.*.product_id' => [
                'required',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'items.*.discount_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate each item has sufficient stock
            if ($this->has('items')) {
                foreach ($this->input('items') as $index => $item) {
                    $product = Product::find($item['product_id'] ?? null);
                    if ($product && $product->quantity_on_hand < ($item['quantity'] ?? 0)) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "Insufficient stock for {$product->name}"
                        );
                    }
                }
            }

            // Validate total sale amount
            $total = $this->calculateTotal();
            if ($total <= 0) {
                $validator->errors()->add(
                    'items',
                    'Sale total must be greater than 0'
                );
            }
        });
    }

    protected function calculateTotal(): float
    {
        $subtotal = 0;
        foreach ($this->input('items', []) as $item) {
            $subtotal += ($item['quantity'] * $item['unit_price']);
        }
        return $subtotal;
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'Selected customer must be active.',
            'payment_method.required' => 'Please select a payment method.',
            'items.required' => 'Please add at least one product to the sale.',
            'items.min' => 'Add at least one product.',
            'items.max' => 'Cannot add more than 50 items per sale.',
            'items.*.product_id.distinct' => 'Duplicate products are not allowed.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'Unit price must be greater than 0.',
        ];
    }
}