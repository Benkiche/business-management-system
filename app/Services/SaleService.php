<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaleService
{
	public function createSale(array $data): Sale
	{
		return DB::transaction(function () use ($data) {
			$subtotal = 0;
			$items = [];

			foreach ($data['items'] as $itemData) {
				$product = \App\Models\Product::lockForUpdate()->findOrFail($itemData['product_id']);
				$quantity = (int) $itemData['quantity'];

				if ($product->quantity_on_hand < $quantity) {
					throw new \RuntimeException("Insufficient stock for {$product->name}.");
				}

				$lineSubtotal = $quantity * (float) $itemData['unit_price'];
				$itemDiscount = $lineSubtotal * ((float) ($itemData['discount_percent'] ?? 0) / 100);
				$lineTotal = $lineSubtotal - $itemDiscount;
				$subtotal += $lineTotal;

				$items[] = [
					'product' => $product,
					'quantity' => $quantity,
					'unit_price' => $itemData['unit_price'],
					'discount' => $itemDiscount,
					'line_total' => $lineTotal,
				];
			}

			$discountPercent = (float) ($data['discount_percent'] ?? 0);
			$discountAmount = $subtotal * ($discountPercent / 100);
			$afterDiscount = $subtotal - $discountAmount;
			$taxPercent = (float) ($data['tax_percent'] ?? 0);
			$taxAmount = $afterDiscount * ($taxPercent / 100);
			$grandTotal = $afterDiscount + $taxAmount;
			$isPaid = ($data['payment_method'] ?? null) !== 'credit_sale';

			$sale = Sale::create([
				'invoice_number' => 'INV-' . strtoupper(uniqid()),
				'customer_id' => $data['customer_id'],
				'salesperson_id' => $data['salesperson_id'] ?? auth()->id(),
				'sale_date' => $data['sale_date'] ?? now()->toDateString(),
				'due_date' => $data['due_date'] ?? ($isPaid ? null : now()->addDays(30)->toDateString()),
				'subtotal' => $subtotal,
				'discount_percent' => $discountPercent,
				'discount_amount' => $discountAmount,
				'tax_percent' => $taxPercent,
				'tax_amount' => $taxAmount,
				'grand_total' => $grandTotal,
				'amount_paid' => $isPaid ? $grandTotal : 0,
				'payment_method' => $data['payment_method'],
				'payment_status' => $isPaid ? 'paid' : 'pending',
				'status' => 'completed',
				'notes' => $data['notes'] ?? null,
			]);

			foreach ($items as $item) {
				$sale->items()->create([
					'product_id' => $item['product']->id,
					'quantity' => $item['quantity'],
					'unit_price' => $item['unit_price'],
					'discount' => $item['discount'],
					'line_total' => $item['line_total'],
				]);

				$item['product']->decrement('quantity_on_hand', $item['quantity']);
				InventoryMovement::create([
					'product_id' => $item['product']->id,
					'movement_type' => 'sale',
					'quantity' => -$item['quantity'],
					'unit_cost' => $item['product']->purchase_price,
					'reference_type' => Sale::class,
					'reference_id' => $sale->id,
					'created_by' => $data['salesperson_id'] ?? auth()->id(),
					'movement_date' => now(),
				]);
			}

			if (! $isPaid) {
				$sale->customer()->increment('outstanding_balance', $grandTotal);
			}

			return $sale->load('customer', 'salesperson', 'items.product');
		});
	}

	public function cancelSale($sale): void
	{
		if (Schema::hasTable('sales')) {
			$sale->update(['status' => 'cancelled']);
		}
	}

	public function getSalesByPaymentMethod($fromDate, $toDate): array { return []; }
	public function getTopSellingProducts($fromDate, $toDate): array { return []; }
	public function getDailySalesTrend($fromDate, $toDate): array { return []; }

	public function getSalesSummary($fromDate, $toDate): array
	{
		if (! Schema::hasTable('sales')) {
			return $this->emptySummary($fromDate, $toDate);
		}

		$sales = DB::table('sales')
			->where('status', 'completed')
			->whereBetween('sale_date', [$fromDate, $toDate]);

		$revenue = (float) $sales->sum('grand_total');
		$discount = (float) $sales->sum('discount_amount');
		$tax = (float) $sales->sum('tax_amount');

		return [
			'period_from' => $fromDate,
			'period_to' => $toDate,
			'total_revenue' => $revenue,
			'total_cost' => 0,
			'gross_profit' => $revenue,
			'profit_margin' => $revenue > 0 ? 100 : 0,
			'total_discount' => $discount,
			'total_tax' => $tax,
		];
	}

	public function getTodaySales(string $date): float
	{
		if (! Schema::hasTable('sales')) {
			return 0;
		}

		return (float) DB::table('sales')
			->where('status', 'completed')
			->whereDate('sale_date', $date)
			->sum('grand_total');
	}

	public function getTotalPaid(): float
	{
		return Schema::hasTable('sales')
			? (float) DB::table('sales')->sum('amount_paid')
			: 0;
	}

	public function getTotalReceived(): float
	{
		return Schema::hasTable('sales')
			? (float) DB::table('sales')->where('payment_status', 'paid')->sum('grand_total')
			: 0;
	}

	public function getOverdueDebts(string $date): float
	{
		if (! Schema::hasTable('sales')) {
			return 0;
		}

		return (float) DB::table('sales')
			->where('status', 'completed')
			->where('payment_status', '!=', 'paid')
			->where('due_date', '<', $date)
			->sum(DB::raw('grand_total - amount_paid'));
	}

	private function emptySummary($fromDate, $toDate): array
	{
		return [
			'period_from' => $fromDate,
			'period_to' => $toDate,
			'total_revenue' => 0,
			'total_cost' => 0,
			'gross_profit' => 0,
			'profit_margin' => 0,
			'total_discount' => 0,
			'total_tax' => 0,
		];
	}
}
