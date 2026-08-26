<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermission('products.edit');
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'unique:products,sku,' . $productId],
            'barcode' => ['nullable', 'string', 'unique:products,barcode,' . $productId],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'quantity_on_hand' => ['required', 'integer', 'min:0'],
            'minimum_stock_level' => ['required', 'integer', 'min:0'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.unique' => 'SKU already exists.',
            'barcode.unique' => 'Barcode already exists.',
            'category_id.required' => 'Please select a category.',
            'supplier_id.required' => 'Please select a supplier.',
            'purchase_price.required' => 'Purchase price is required.',
            'selling_price.required' => 'Selling price is required.',
            'quantity_on_hand.required' => 'Quantity on hand is required.',
            'product_image.image' => 'File must be an image.',
            'product_image.max' => 'Image size must not exceed 2MB.',
        ];
    }
}