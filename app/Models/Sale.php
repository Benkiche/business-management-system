<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
	use HasFactory;

	protected $fillable = ['invoice_number', 'customer_id', 'salesperson_id', 'sale_date', 'due_date', 'subtotal', 'discount_percent', 'discount_amount', 'tax_percent', 'tax_amount', 'grand_total', 'amount_paid', 'payment_method', 'payment_status', 'status', 'cancelled_at', 'notes'];

	protected $casts = ['sale_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'grand_total' => 'decimal:2', 'amount_paid' => 'decimal:2'];

	public function customer() { return $this->belongsTo(Customer::class); }
	public function salesperson() { return $this->belongsTo(User::class, 'salesperson_id'); }
	public function items() { return $this->hasMany(SaleItem::class); }
	public function payments() { return $this->hasMany(Payment::class); }
	public function scopeCompleted($query) { return $query->where('status', 'completed'); }
	public function scopeDateRange($query, $from, $to) { return $query->whereBetween('sale_date', [$from, $to]); }
	public function isCancelled(): bool { return $this->status === 'cancelled'; }
	public function isPaid(): bool { return $this->payment_status === 'paid'; }
	public function getOutstandingBalanceAttribute(): float { return (float) $this->grand_total - (float) $this->amount_paid; }
}
