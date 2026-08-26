<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable exception handling for tests
        $this->withoutExceptionHandling();
    }

    /**
     * Create an authenticated user for testing.
     */
    protected function createAuthenticatedUser($role = 'admin', $attributes = [])
    {
        $user = \App\Models\User::factory()->create($attributes);
        
        if ($role) {
            $roleModel = \App\Models\Role::where('name', $role)->first();
            if ($roleModel) {
                $user->role_id = $roleModel->id;
                $user->save();
            }
        }

        return $user;
    }

    /**
     * Login a user.
     */
    protected function loginUser($user = null)
    {
        $user = $user ?? $this->createAuthenticatedUser();
        $this->actingAs($user);
        return $user;
    }

    /**
     * Create a complete sale with items for testing.
     */
    protected function createTestSale($attributes = [])
    {
        $customer = \App\Models\Customer::factory()->create();
        $products = \App\Models\Product::factory()->count(3)->create();

        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => $product->selling_price,
                'discount_percent' => 0,
            ];
        }

        $saleData = array_merge([
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'sale_date' => now()->toDateString(),
            'items' => $items,
        ], $attributes);

        return app(\App\Services\SaleService::class)->createSale($saleData + [
            'salesperson_id' => auth()->id() ?? 1,
        ]);
    }

    /**
     * Create a complete expense for testing.
     */
    protected function createTestExpense($attributes = [])
    {
        $category = \App\Models\ExpenseCategory::factory()->create();

        return \App\Models\Expense::factory()->create(array_merge([
            'expense_category_id' => $category->id,
        ], $attributes));
    }
}