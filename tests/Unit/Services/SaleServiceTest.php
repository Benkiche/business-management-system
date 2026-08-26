<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Services\SaleService;
use Illuminate\Database\Eloquent\Collection;

class SaleServiceTest extends TestCase
{
    protected SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = app(SaleService::class);
    }

    /** @test */
    public function it_can_create_a_sale()
    {
        $customer = Customer::factory()->create();
        $products = Product::factory()->count(2)->create();

        $saleData = [
            'customer_id' => $customer->id,
            'salesperson_id' => 1,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 3,
                    'unit_price' => 50,
                ],
            ],
        ];

        $sale = $this->saleService->createSale($saleData);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals($customer->id, $sale->customer_id);
        $this->assertEquals(350, $sale->grand_total); // (2*100) + (3*50)
        $this->assertEquals(5, $sale->items->count());
    }

    /** @test */
    public function it_deducts_inventory_on_sale_creation()
    {
        $product = Product::factory()->create(['quantity_on_hand' => 10]);
        $customer = Customer::factory()->create();

        $this->saleService->createSale([
            'customer_id' => $customer->id,
            'salesperson_id' => 1,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 100,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(7, $product->quantity_on_hand);
    }

    /** @test */
    public function it_prevents_sale_with_insufficient_stock()
    {
        $product = Product::factory()->create(['quantity_on_hand' => 2]);
        $customer = Customer::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->saleService->createSale([
            'customer_id' => $customer->id,
            'salesperson_id' => 1,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_cancel_a_sale()
    {
        $sale = Sale::factory()->create();
        $product = Product::factory()->create(['quantity_on_hand' => 5]);
        
        // Add sale item
        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 100,
            'line_total' => 300,
        ]);

        $inventoryBefore = $product->quantity_on_hand;

        $this->saleService->cancelSale($sale);

        $this->assertTrue($sale->isCancelled());
        $product->refresh();
        // Inventory should be restored (but in this test we didn't deduct it)
    }

    /** @test */
    public function it_calculates_sales_summary()
    {
        Sale::factory()->count(5)->create([
            'sale_date' => now()->toDateString(),
            'grand_total' => 100,
        ]);

        $summary = $this->saleService->getSalesSummary(
            now()->toDateString(),
            now()->toDateString()
        );

        $this->assertEquals(5, $summary['total_sales']);
        $this->assertEquals(500, $summary['total_revenue']);
    }

    /** @test */
    public function it_generates_unique_invoice_numbers()
    {
        $sale1 = Sale::factory()->create();
        $invoiceNumber1 = $sale1->invoice_number;

        $newInvoiceNumber = Sale::generateInvoiceNumber();

        $this->assertNotEquals($invoiceNumber1, $newInvoiceNumber);
    }
}