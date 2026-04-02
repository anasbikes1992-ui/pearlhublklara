<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sme_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('sme_businesses')->cascadeOnDelete();
            $table->enum('plan', ['silver', 'gold', 'platinum']);
            $table->integer('product_limit'); // 100, 500, or unlimited (-1)
            $table->decimal('price_paid', 10, 2);
            $table->enum('status', ['active', 'expired', 'suspended', 'cancelled'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->string('payment_ref')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sme_subscriptions');
    }
};
