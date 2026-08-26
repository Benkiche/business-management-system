<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->enum('movement_type', ['stock_in', 'stock_out', 'adjustment', 'sale', 'return']);
            $table->integer('quantity'); // Can be positive or negative
            $table->decimal('unit_cost', 15, 2)->nullable(); // Cost at time of movement
            $table->string('reference_type')->nullable(); // PurchaseOrder, Sale, Adjustment, etc.
            $table->bigInteger('reference_id')->nullable(); // ID of related record
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();

            // Indexes for performance
            $table->index('product_id');
            $table->index('movement_date');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};