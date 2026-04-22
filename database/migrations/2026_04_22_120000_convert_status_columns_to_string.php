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
        // Convert bookings.status from ENUM to string
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_status_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 32)->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
        });

        // Convert subscriptions.status from ENUM to string
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_user_id_status_index');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('status', 32)->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        // Convert subscriptions.type from ENUM to string
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('type', 32)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert bookings.status back to ENUM
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_status_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'completed', 'canceled'])->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
        });

        // Revert subscriptions.status back to ENUM
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_user_id_status_index');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'expired', 'canceled'])->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        // Revert subscriptions.type back to ENUM
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('type', ['trial', 'paid'])->change();
        });
    }
};
