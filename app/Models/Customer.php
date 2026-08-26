<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'credit_limit',
        'outstanding_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    /**
     * Get all sales made to this customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get all payments made by this customer.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if customer has exceeded credit limit.
     */
    public function hasExceededCreditLimit(): bool
    {
        return $this->outstanding_balance > $this->credit_limit;
    }

    /**
     * Get available credit for this customer.
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->outstanding_balance);
    }

    /**
     * Scope to get only active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get customers with outstanding balance.
     */
    public function scopeWithOutstandingBalance($query)
    {
        return $query->where('outstanding_balance', '>', 0);
    }

    /**
     * Generate unique customer code.
     */
    public static function generateCustomerCode()
    {
        $latestCustomer = self::latest('id')->first();
        $number = $latestCustomer ? $latestCustomer->id + 1 : 1;
        return 'CUST-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}