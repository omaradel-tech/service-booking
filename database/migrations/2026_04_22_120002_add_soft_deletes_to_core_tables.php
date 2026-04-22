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
        // Add soft deletes to services table
        Schema::table('services', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at');
        });

        // Add soft deletes to packages table
        Schema::table('packages', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at');
        });

        // Add soft deletes to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at');
        });

        // Add soft deletes to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from services table
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_deleted_at_index');
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from packages table
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('packages_deleted_at_index');
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_deleted_at_index');
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_deleted_at_index');
            $table->dropSoftDeletes();
        });
    }
};
