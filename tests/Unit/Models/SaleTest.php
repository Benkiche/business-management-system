<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Sale;

class SaleTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_sale_is_paid()
    {
        $sale = Sale::factory()->paid()->create();

        $this->assertTrue($sale->isPaid());
        $this->assertFalse($sale->isPartiallyPaid());
        $this->assertFalse($sale->isUnpaid());
    }

    /** @test */
    public function it_can_determine_if_sale_is_partially_paid()
    {
        $sale = Sale::factory()->partiallyPaid()->create();

        $this->assertFalse($sale->isPaid());
        $this->assertTrue($sale->isPartiallyPaid());
        $this->assertFalse($sale->isUnpaid());
    }

    /** @test */
    public function it_can_determine_if_sale_is_unpaid()
    {
        $sale = Sale::factory()->create(['amount_paid' => 0]);

        $this->assertFalse($sale->isPaid());
        $this->assertFalse($sale->isPartiallyPaid());
        $this->assertTrue($sale->isUnpaid());
    }

    /** @test */
    public function it_calculates_outstanding_balance()
    {
        $sale = Sale::factory()->create([
            'grand_total' => 1000,
            'amount_paid' => 300,
        ]);

        $this->assertEquals(700, $sale->outstanding_balance);
    }

    /** @test */
    public function it_can_be_marked_as_cancelled()
    {
        $sale = Sale::factory()->create();

        $this->assertFalse($sale->isCancelled());

        $sale->update(['status' => 'cancelled']);

        $this->assertTrue($sale->isCancelled());
    }

    /** @test */
    public function it_generates_unique_invoice_numbers()
    {
        $sale1 = Sale::factory()->create();
        $sale2 = Sale::factory()->create();

        $this->assertNotEquals($sale1->invoice_number, $sale2->invoice_number);
    }

    /** @test */
    public function it_scopes_by_date_range()
    {
        Sale::factory()->create(['sale_date' => now()->subDays(10)]);
        Sale::factory()->create(['sale_date' => now()]);
        Sale::factory()->create(['sale_date' => now()->addDays(10)]);

        $sales = Sale::dateRange(now()->toDateString(), now()->toDateString())->get();

        $this->assertEquals(1, $sales->count());
    }
}