<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;

class SaleControllerTest extends TestCase
{
    /** @test */
    public function authorized_user_can_view_sales_list()
    {
        $user = $this->loginUser();

        Sale::factory()->count(3)->create();

        $response = $this->get('/sales');

        $response->assertStatus(200);
        $response->assertViewIs('sales.index');
    }

    /** @test */
    public function unauthorized_user_cannot_view_sales_list()
    {
        $this->get('/sales')->assertRedirect('/login');
    }

    /** @test */
    public function user_can_create_sale()
    {
        $user = $this->loginUser();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['quantity_on_hand' => 10]);

        $response = $this->post('/sales', [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'sale_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                ],
            ],
        ]);

        $response->assertRedirect('/sales/*');
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
        ]);
    }

    /** @test */
    public function sale_creation_validates_required_fields()
    {
        $user = $this->loginUser();

        $response = $this->post('/sales', []);

        $response->assertSessionHasErrors(['customer_id', 'payment_method', 'items']);
    }

    /** @test */
    public function user_can_view_sale_details()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->create();

        $response = $this->get("/sales/{$sale->id}");

        $response->assertStatus(200);
        $response->assertViewIs('sales.show');
        $response->assertViewHas('sale');
    }

    /** @test */
    public function user_can_cancel_sale()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->create();

        $response = $this->post("/sales/{$sale->id}/cancel");

        $response->assertRedirect("/sales/{$sale->id}");
        $this->assertTrue($sale->fresh()->isCancelled());
    }

    /** @test */
    public function user_cannot_cancel_paid_sale()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->paid()->create();

        $response = $this->post("/sales/{$sale->id}/cancel");

        $response->assertSessionHasErrors();
        $this->assertFalse($sale->fresh()->isCancelled());
    }

    /** @test */
    public function user_can_filter_sales_by_status()
    {
        $user = $this->loginUser();
        Sale::factory()->create(['status' => 'completed']);
        Sale::factory()->cancelled()->create();

        $response = $this->get('/sales?status=completed');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_search_sales()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->create();

        $response = $this->get("/sales?search={$sale->invoice_number}");

        $response->assertStatus(200);
        $response->assertSee($sale->invoice_number);
    }

    /** @test */
    public function user_can_export_sales_report()
    {
        $user = $this->loginUser();
        Sale::factory()->count(5)->create();

        $response = $this->get('/sales/report/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }
}