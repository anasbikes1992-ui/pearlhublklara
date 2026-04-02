<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('listing_id')->nullable();
            $table->string('listing_type')->nullable();
            $table->string('channel')->nullable(); // chat.{listing_id}.{sender_id}.{receiver_id}
            $table->foreignUuid('sender_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('receiver_id')->constrained('profiles')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_voice')->default(false);
            $table->string('voice_url')->nullable();
            $table->text('original_text')->nullable();
            $table->text('translated_text')->nullable();
            $table->string('original_lang', 5)->default('en');
            $table->string('target_lang', 5)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['listing_id', 'listing_type']);
            $table->index(['sender_id', 'receiver_id']);
            $table->index('channel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
