<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\Customer;

class PaymentControllerTest extends TestCase
{
    /** @test */
    public function user_can_record_payment_for_sale()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->create(['grand_total' => 1000, 'amount_paid' => 0]);

        $response = $this->post("/payments/sale/{$sale->id}", [
            'amount' => 500,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertRedirect("/sales/{$sale->id}");
        $sale->refresh();
        $this->assertEquals(500, $sale->amount_paid);
        $this->assertEquals('partial', $sale->payment_status);
    }

    /** @test */
    public function user_cannot_record_payment_exceeding_balance()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->create(['grand_total' => 1000, 'amount_paid' => 0]);

        $response = $this->post("/payments/sale/{$sale->id}", [
            'amount' => 1500,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function user_cannot_pay_already_paid_sale()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->paid()->create();

        $response = $this->get("/payments/sale/{$sale->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function full_payment_updates_status_to_paid()
    {
        $user = $this->loginUser();
        $sale = Sale::factory()->create(['grand_total' => 1000, 'amount_paid' => 0]);

        $this->post("/payments/sale/{$sale->id}", [
            'amount' => 1000,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $sale->refresh();
        $this->assertTrue($sale->isPaid());
        $this->assertEquals('paid', $sale->payment_status);
    }

    /** @test */
    public function user_can_record_general_payment()
    {
        $user = $this->loginUser();
        $customer = Customer::factory()->withDebt(1000)->create();

        $response = $this->post('/payments/general', [
            'customer_id' => $customer->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $customer->refresh();
        $this->assertEquals(500, $customer->outstanding_balance);
    }

    /** @test */
    public function payment_history_shows_all_customer_payments()
    {
        $user = $this->loginUser();
        $customer = Customer::factory()->create();

        $response = $this->get("/payments/customer/{$customer->id}");

        $response->assertStatus(200);
        $response->assertViewHas('payments');
    }
}