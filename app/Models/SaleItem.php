<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
	use HasFactory;

	protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price', 'discount', 'line_total'];

	protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'discount' => 'decimal:2', 'line_total' => 'decimal:2'];

	public function sale() { return $this->belongsTo(Sale::class); }
	public function product() { return $this->belongsTo(Product::class); }
}
