<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index(['provider_id', 'status']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'type', 'status']);
        });

        Schema::table('earnings', function (Blueprint $table) {
            $table->index(['provider_id', 'status', 'created_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['channel', 'created_at']);
            $table->index(['receiver_id', 'is_read']);
        });

        Schema::table('taxi_rides', function (Blueprint $table) {
            $table->index(['driver_id', 'status']);
            $table->index(['rider_id', 'status']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['listing_id', 'listing_type']);
        });

        Schema::table('sme_products', function (Blueprint $table) {
            $table->index(['business_id', 'available']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['provider_id', 'status']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'status']);
        });

        Schema::table('earnings', function (Blueprint $table) {
            $table->dropIndex(['provider_id', 'status', 'created_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['channel', 'created_at']);
            $table->dropIndex(['receiver_id', 'is_read']);
        });

        Schema::table('taxi_rides', function (Blueprint $table) {
            $table->dropIndex(['driver_id', 'status']);
            $table->dropIndex(['rider_id', 'status']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['listing_id', 'listing_type']);
        });

        Schema::table('sme_products', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'available']);
        });
    }
};
