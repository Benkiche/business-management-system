<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('chat_enabled')->default(true)->after('status');
            $table->enum('online_status', ['online', 'away', 'offline', 'do_not_disturb'])->default('offline')->after('chat_enabled');
            $table->timestamp('last_seen_at')->nullable()->after('online_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chat_enabled', 'online_status', 'last_seen_at']);
        });
    }
};