<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Composite index for status and scheduled_at (common query pattern)
            $table->index(['status', 'scheduled_at']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            // Composite index for status and grace_ends_at (for expiry queries)
            $table->index(['status', 'grace_ends_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            // Composite index for is_active and duration_minutes (for overlap checks)
            $table->index(['is_active', 'duration_minutes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'scheduled_at']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['status', 'grace_ends_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'duration_minutes']);
        });
    }
};
