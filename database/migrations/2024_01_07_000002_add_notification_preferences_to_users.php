<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->default(json_encode([
                'low_stock' => true,
                'overdue_payment' => true,
                'payment_received' => true,
                'sale_created' => true,
                'expense_approved' => true,
                'email_notifications' => true,
            ]));
            $table->timestamp('last_notification_read_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notification_preferences', 'last_notification_read_at']);
        });
    }
};