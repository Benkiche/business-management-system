<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchasePrice = $this->faker->randomFloat(2, 10, 100);

        return [
            'product_code' => 'PRD-' . $this->faker->unique()->numberBetween(100000, 999999),
            'category_id' => \App\Models\Category::factory(),
            'supplier_id' => \App\Models\Supplier::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'purchase_price' => $purchasePrice,
            'selling_price' => $purchasePrice * $this->faker->randomFloat(2, 1.2, 2.5),
            'quantity_on_hand' => $this->faker->numberBetween(10, 100),
            'minimum_stock_level' => $this->faker->numberBetween(5, 20),
            'status' => 'active',
        ];
    }

    /**
     * Product with low stock.
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity_on_hand' => 3,
                'minimum_stock_level' => 10,
            ];
        });
    }

    /**
     * Out of stock product.
     */
    public function outOfStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity_on_hand' => 0,
            ];
        });
    }

    /**
     * Inactive product.
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }
}