<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance profiles with language + tax settings
        Schema::table('profiles', function (Blueprint $table) {
            $table->jsonb('tax_settings')->nullable()->after('preferred_language');
            $table->boolean('is_tax_registered')->default(false)->after('tax_settings');
            $table->string('tax_id')->nullable()->after('is_tax_registered');
        });

        // Enhance sme_products with variants + category + stock
        Schema::table('sme_products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->jsonb('variants')->nullable()->after('price');
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'low_stock'])->default('in_stock')->after('available');
            $table->string('sku')->nullable()->after('stock_status');
        });

        // Enhance stays with availability calendar + seo
        Schema::table('stays_listings', function (Blueprint $table) {
            $table->jsonb('availability_calendar')->nullable()->after('active');
            $table->string('seo_slug')->nullable()->unique()->after('availability_calendar');
        });

        // Enhance vehicles with seo
        Schema::table('vehicles_listings', function (Blueprint $table) {
            $table->string('seo_slug')->nullable()->unique()->after('active');
        });

        // Enhance events with seo
        Schema::table('events_listings', function (Blueprint $table) {
            $table->string('seo_slug')->nullable()->unique()->after('active');
        });

        // Enhance properties with seo
        Schema::table('properties_listings', function (Blueprint $table) {
            $table->string('seo_slug')->nullable()->unique()->after('active');
        });

        // Enhance sme_businesses with seo + subscription
        Schema::table('sme_businesses', function (Blueprint $table) {
            $table->string('seo_slug')->nullable()->unique()->after('active');
            $table->enum('subscription_plan', ['free', 'silver', 'gold', 'platinum'])->default('free')->after('seo_slug');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['tax_settings', 'is_tax_registered', 'tax_id']);
        });
        Schema::table('sme_products', function (Blueprint $table) {
            $table->dropColumn(['category', 'variants', 'stock_status', 'sku']);
        });
        Schema::table('stays_listings', function (Blueprint $table) {
            $table->dropColumn(['availability_calendar', 'seo_slug']);
        });
        Schema::table('vehicles_listings', function (Blueprint $table) {
            $table->dropColumn('seo_slug');
        });
        Schema::table('events_listings', function (Blueprint $table) {
            $table->dropColumn('seo_slug');
        });
        Schema::table('properties_listings', function (Blueprint $table) {
            $table->dropColumn('seo_slug');
        });
        Schema::table('sme_businesses', function (Blueprint $table) {
            $table->dropColumn(['seo_slug', 'subscription_plan']);
        });
    }
};
