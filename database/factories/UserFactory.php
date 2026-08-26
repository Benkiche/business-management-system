<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role_id' => 1,
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a manager.
     */
    public function manager(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'role_id' => \App\Models\Role::where('name', 'manager')->first()?->id ?? 3,
            ];
        });
    }

    /**
     * Indicate that the user is a salesperson.
     */
    public function salesperson(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'role_id' => \App\Models\Role::where('name', 'salesperson')->first()?->id ?? 4,
            ];
        });
    }

    /**
     * Indicate that the user is inactive.
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