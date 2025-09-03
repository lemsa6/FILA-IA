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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->string('context');
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->uuid('request_id')->nullable();
            $table->uuid('api_key_id')->nullable();
            $table->timestamp('created_at');
            
            // Índices para melhor performance em consultas
            $table->index('level');
            $table->index('context');
            $table->index('created_at');
            $table->index('request_id');
            $table->index('api_key_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
}; 