<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'name',
        'description',
        'sku',
        'barcode',
        'category_id',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'quantity_on_hand',
        'minimum_stock_level',
        'product_image_path',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    /**
     * Get the category of this product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier of this product.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get inventory movements for this product.
     */
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->minimum_stock_level;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand === 0;
    }

    /**
     * Get profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->purchase_price == 0) {
            return 0;
        }
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_on_hand <= minimum_stock_level');
    }

    /**
     * Scope to get out of stock products.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', 0);
    }

    /**
     * Generate unique product code.
     */
    public static function generateProductCode()
    {
        $latestProduct = self::latest('id')->first();
        $number = $latestProduct ? $latestProduct->id + 1 : 1;
        return 'PRD-' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get total inventory value (quantity * purchase price).
     */
    public function getInventoryValueAttribute(): float
    {
        return (float)($this->quantity_on_hand * $this->purchase_price);
    }

    /**
     * Get total retail value (quantity * selling price).
     */
    public function getRetailValueAttribute(): float
    {
        return (float)($this->quantity_on_hand * $this->selling_price);
    }

    /**
     * Get average cost per unit from recent movements.
     */
    public function getAverageCostAttribute(): float
    {
        $movements = $this->inventoryMovements()
            ->where('unit_cost', '!=', null)
            ->sum('unit_cost');
        
        $count = $this->inventoryMovements()
            ->where('unit_cost', '!=', null)
            ->count();

        return $count > 0 ? $movements / $count : $this->purchase_price;
    }
}