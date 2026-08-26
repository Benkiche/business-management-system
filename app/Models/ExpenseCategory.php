<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Get all expenses in this category.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get total expenses for this category.
     */
    public function getTotalExpensesAttribute(): float
    {
        return (float)$this->expenses()->sum('amount');
    }

    /**
     * Get expenses count.
     */
    public function getExpensesCountAttribute(): int
    {
        return $this->expenses()->count();
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}