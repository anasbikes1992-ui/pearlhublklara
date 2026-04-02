<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_concierge_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->text('query');
            $table->text('response');
            $table->string('provider')->default('anthropic'); // anthropic, grok
            $table->string('model')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->integer('response_time_ms')->default(0);
            $table->string('session_id')->nullable();
            $table->jsonb('context')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_concierge_logs');
    }
};
