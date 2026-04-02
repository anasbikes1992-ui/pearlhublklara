<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_sales_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('business_id')->nullable()->constrained('sme_businesses')->nullOnDelete();
            $table->string('month', 7); // YYYY-MM
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(6.5);
            $table->decimal('commission_due', 10, 2)->default(0);
            $table->decimal('tax_applied', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('sscl_amount', 10, 2)->default(0);
            $table->integer('inquiry_count')->default(0);
            $table->boolean('verified')->default(false);
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'month']);
            $table->index('verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_sales_reports');
    }
};
