<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientStockRule implements ValidationRule
{
    protected $productId;
    protected $quantity;

    public function __construct($productId, $quantity)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $product = Product::find($this->productId);

        if (!$product || $product->quantity_on_hand < $this->quantity) {
            $fail("Insufficient stock for " . ($product?->name ?? 'product'));
        }
    }
}