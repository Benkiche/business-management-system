<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'customer_code' => 'CUST-' . $this->faker->unique()->numberBetween(100000, 999999),
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zip_code' => $this->faker->postcode(),
            'credit_limit' => $this->faker->randomFloat(2, 1000, 10000),
            'outstanding_balance' => 0,
            'status' => 'active',
        ];
    }

    /**
     * Customer with outstanding balance.
     */
    public function withDebt($amount = null): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $debt = $amount ?? $this->faker->randomFloat(2, 500, 5000);
            return [
                'outstanding_balance' => $debt,
            ];
        });
    }

    /**
     * Customer at credit limit.
     */
    public function atCreditLimit(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'outstanding_balance' => $attributes['credit_limit'],
            ];
        });
    }

    /**
     * Inactive customer.
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