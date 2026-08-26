<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Record stock in (receipt of goods).
     *
     * @throws Exception
     */
    public function stockIn(
        Product $product,
        int $quantity,
        ?float $unitCost = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new Exception('Stock in quantity must be greater than 0.');
        }

        return DB::transaction(function () use ($product, $quantity, $unitCost, $notes, $userId) {
            // Create inventory movement
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'stock_in',
                'quantity' => $quantity,
                'unit_cost' => $unitCost ?? $product->purchase_price,
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
                'movement_date' => now(),
            ]);

            // Update product quantity
            $product->increment('quantity_on_hand', $quantity);

            return $movement;
        });
    }

    /**
     * Record stock out (goods removed).
     *
     * @throws Exception
     */
    public function stockOut(
        Product $product,
        int $quantity,
        ?string $reason = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new Exception('Stock out quantity must be greater than 0.');
        }

        if ($product->quantity_on_hand < $quantity) {
            throw new Exception(
                "Insufficient stock. Available: {$product->quantity_on_hand}, Requested: {$quantity}"
            );
        }

        return DB::transaction(function () use ($product, $quantity, $reason, $notes, $userId) {
            // Create inventory movement (negative quantity)
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'stock_out',
                'quantity' => -$quantity,
                'reference_type' => $reason,
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
                'movement_date' => now(),
            ]);

            // Update product quantity
            $product->decrement('quantity_on_hand', $quantity);

            return $movement;
        });
    }

    /**
     * Adjust inventory (reconciliation).
     *
     * @throws Exception
     */
    public function adjustInventory(
        Product $product,
        int $newQuantity,
        ?string $reason = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        $currentQuantity = $product->quantity_on_hand;
        $difference = $newQuantity - $currentQuantity;

        if ($difference === 0) {
            throw new Exception('Adjustment quantity must be different from current quantity.');
        }

        return DB::transaction(function () use ($product, $newQuantity, $difference, $reason, $notes, $userId) {
            // Create inventory movement
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'quantity' => $difference,
                'reference_type' => $reason ?? 'Physical Count',
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
                'movement_date' => now(),
            ]);

            // Update product quantity
            $product->update(['quantity_on_hand' => $newQuantity]);

            return $movement;
        });
    }

    /**
     * Record sale movement (called when sale is created).
     *
     * @throws Exception
     */
    public function recordSale(
        Product $product,
        int $quantity,
        float $unitCost = null,
        int $saleId = null,
        ?int $userId = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new Exception('Sale quantity must be greater than 0.');
        }

        if ($product->quantity_on_hand < $quantity) {
            throw new Exception(
                "Insufficient stock for sale. Available: {$product->quantity_on_hand}, Required: {$quantity}"
            );
        }

        return DB::transaction(function () use ($product, $quantity, $unitCost, $saleId, $userId) {
            // Create inventory movement
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'sale',
                'quantity' => -$quantity,
                'unit_cost' => $unitCost ?? $product->purchase_price,
                'reference_type' => 'Sale',
                'reference_id' => $saleId,
                'created_by' => $userId ?? auth()->id(),
                'movement_date' => now(),
            ]);

            // Update product quantity
            $product->decrement('quantity_on_hand', $quantity);

            return $movement;
        });
    }

    /**
     * Reverse a movement (e.g., cancel a sale).
     *
     * @throws Exception
     */
    public function reverseMovement(InventoryMovement $movement): InventoryMovement {
        if (!in_array($movement->movement_type, ['stock_in', 'stock_out', 'adjustment', 'sale'])) {
            throw new Exception('Cannot reverse this type of movement.');
        }

        return DB::transaction(function () use ($movement) {
            $product = $movement->product;

            // Reverse the quantity
            $reverseQuantity = -$movement->quantity;

            // Create reverse movement
            $reversal = InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => $movement->movement_type,
                'quantity' => $reverseQuantity,
                'unit_cost' => $movement->unit_cost,
                'reference_type' => 'Reversal',
                'reference_id' => $movement->id,
                'notes' => "Reversal of movement #{$movement->id}",
                'created_by' => auth()->id(),
                'movement_date' => now(),
            ]);

            // Update product quantity
            if ($reverseQuantity > 0) {
                $product->increment('quantity_on_hand', $reverseQuantity);
            } else {
                $product->decrement('quantity_on_hand', abs($reverseQuantity));
            }

            return $reversal;
        });
    }

    /**
     * Get inventory summary for a product.
     */
    public function getInventorySummary(Product $product): array
    {
        $movements = $product->inventoryMovements()->get();
        
        $stockIn = $movements->where('movement_type', 'stock_in')->sum('quantity');
        $stockOut = $movements->where('movement_type', 'stock_out')->sum('quantity');
        $adjustments = $movements->where('movement_type', 'adjustment')->sum('quantity');
        $sales = $movements->where('movement_type', 'sale')->sum('quantity');

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'current_quantity' => $product->quantity_on_hand,
            'stock_in_total' => $stockIn,
            'stock_out_total' => $stockOut,
            'adjustments_total' => $adjustments,
            'sales_total' => abs($sales),
            'inventory_value' => $product->inventory_value,
            'retail_value' => $product->retail_value,
            'movement_count' => $movements->count(),
        ];
    }

    /**
     * Get inventory valuation report.
     */
    public function getInventoryValuationReport(): array
    {
        $products = Product::active()
            ->where('quantity_on_hand', '>', 0)
            ->with('category', 'supplier')
            ->get();

        $totalCost = 0;
        $totalRetail = 0;
        $items = [];

        foreach ($products as $product) {
            $costValue = $product->inventory_value;
            $retailValue = $product->retail_value;

            $items[] = [
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'quantity' => $product->quantity_on_hand,
                'unit_cost' => $product->purchase_price,
                'cost_value' => $costValue,
                'unit_price' => $product->selling_price,
                'retail_value' => $retailValue,
                'profit_margin' => $product->profit_margin,
            ];

            $totalCost += $costValue;
            $totalRetail += $retailValue;
        }

        return [
            'items' => $items,
            'total_cost_value' => $totalCost,
            'total_retail_value' => $totalRetail,
            'total_profit' => $totalRetail - $totalCost,
            'total_products' => count($items),
        ];
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(): array
    {
        return Product::active()
            ->lowStock()
            ->with('category', 'supplier')
            ->orderByRaw('minimum_stock_level - quantity_on_hand DESC')
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'quantity_on_hand' => $product->quantity_on_hand,
                    'minimum_stock_level' => $product->minimum_stock_level,
                    'shortage' => $product->minimum_stock_level - $product->quantity_on_hand,
                    'supplier_name' => $product->supplier->name,
                    'status' => $product->quantity_on_hand <= 0 ? 'out_of_stock' : 'low_stock',
                ];
            })
            ->toArray();
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts(): array
    {
        return Product::active()
            ->outOfStock()
            ->with('category', 'supplier')
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'supplier_name' => $product->supplier->name,
                    'minimum_stock_level' => $product->minimum_stock_level,
                ];
            })
            ->toArray();
    }

    /**
     * Get movement history for a product.
     */
    public function getMovementHistory(Product $product, int $limit = 50): array
    {
        return $product->inventoryMovements()
            ->with('creator')
            ->limit($limit)
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'type' => $movement->getTypeLabel(),
                    'quantity' => $movement->quantity,
                    'unit_cost' => $movement->unit_cost,
                    'total_value' => $movement->quantity * ($movement->unit_cost ?? 0),
                    'reference' => $movement->reference_type . (isset($movement->reference_id) ? "#" . $movement->reference_id : ""),
                    'notes' => $movement->notes,
                    'created_by' => $movement->creator->name,
                    'date' => $movement->movement_date->format('M d, Y H:i'),
                ];
            })
            ->toArray();
    }
}