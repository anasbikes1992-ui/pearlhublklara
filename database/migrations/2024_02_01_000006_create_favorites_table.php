<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('profiles')->cascadeOnDelete();
            $table->uuid('listing_id');
            $table->string('listing_type');
            $table->timestamps();

            $table->unique(['user_id', 'listing_id', 'listing_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
