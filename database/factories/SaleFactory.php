<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $discountPercent = $this->faker->randomFloat(2, 0, 20);
        $discountAmount = $subtotal * ($discountPercent / 100);
        $afterDiscount = $subtotal - $discountAmount;
        $taxPercent = $this->faker->randomFloat(2, 0, 15);
        $taxAmount = $afterDiscount * ($taxPercent / 100);
        $grandTotal = $afterDiscount + $taxAmount;

        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(100000, 999999),
            'customer_id' => \App\Models\Customer::factory(),
            'salesperson_id' => \App\Models\User::factory(),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percent' => $discountPercent,
            'tax_amount' => $taxAmount,
            'tax_percent' => $taxPercent,
            'grand_total' => $grandTotal,
            'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'check', 'bank_transfer']),
            'payment_status' => 'pending',
            'amount_paid' => 0,
            'sale_date' => $this->faker->dateThisMonth(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => 'completed',
        ];
    }

    /**
     * Paid sale.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'paid',
                'amount_paid' => $attributes['grand_total'],
            ];
        });
    }

    /**
     * Partially paid sale.
     */
    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'partial',
                'amount_paid' => $attributes['grand_total'] * 0.5,
            ];
        });
    }

    /**
     * Cancelled sale.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ];
        });
    }
}