<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // alert, warning, info, success
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // fas fa-exclamation-circle
            $table->string('action_url')->nullable(); // Link to related resource
            $table->enum('category', [
                'low_stock',
                'overdue_payment',
                'payment_received',
                'sale_created',
                'expense_approved',
                'customer_created',
                'system_alert',
                'audit_alert'
            ]);
            $table->timestamp('read_at')->nullable();
            $table->boolean('sent_email')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('category');
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};