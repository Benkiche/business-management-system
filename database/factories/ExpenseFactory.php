<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_code' => 'EXP-' . $this->faker->unique()->numberBetween(100000, 999999),
            'expense_category_id' => \App\Models\ExpenseCategory::factory(),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'check', 'bank_transfer']),
            'expense_date' => $this->faker->dateThisMonth(),
            'recorded_by' => \App\Models\User::factory(),
            'status' => 'pending',
        ];
    }

    /**
     * Approved expense.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    /**
     * Rejected expense.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
            ];
        });
    }
}