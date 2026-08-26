<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_code',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'outstanding_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'outstanding_balance' => 'decimal:2',
    ];

    /**
     * Get all products supplied by this supplier.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope to get only active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Generate unique supplier code.
     */
    public static function generateSupplierCode()
    {
        $latestSupplier = self::latest('id')->first();
        $number = $latestSupplier ? $latestSupplier->id + 1 : 1;
        return 'SUP-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}