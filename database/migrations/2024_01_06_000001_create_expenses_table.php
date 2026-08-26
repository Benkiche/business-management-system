<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_code')->unique();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', [
                'cash',
                'credit_card',
                'check',
                'bank_transfer'
            ])->default('cash');
            $table->date('expense_date');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            // Indexes
            $table->unique('expense_code');
            $table->index('expense_category_id');
            $table->index('expense_date');
            $table->index('recorded_by');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};