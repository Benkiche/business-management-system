<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'movement_date',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    /**
     * Get the product of this movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this movement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only inbound movements (stock increases).
     */
    public function scopeInbound($query)
    {
        return $query->whereIn('movement_type', ['stock_in', 'return']);
    }

    /**
     * Scope to get only outbound movements (stock decreases).
     */
    public function scopeOutbound($query)
    {
        return $query->whereIn('movement_type', ['stock_out', 'sale']);
    }

    /**
     * Scope to filter by movement type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('movement_date', [$from, $to]);
    }

    /**
     * Scope to get movements for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get movement type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->movement_type) {
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'sale' => 'Sale',
            'return' => 'Return',
            default => $this->movement_type,
        };
    }

    /**
     * Determine if movement is an increase.
     */
    public function isIncrease(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Determine if movement is a decrease.
     */
    public function isDecrease(): bool
    {
        return $this->quantity < 0;
    }
}