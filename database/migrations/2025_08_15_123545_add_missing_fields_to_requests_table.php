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
        Schema::table('requests', function (Blueprint $table) {
            // Adicionar campos que estão faltando para completar o modelo
            $table->string('session_id')->nullable()->after('api_key_id');
            $table->json('parameters')->nullable()->after('content');
            $table->json('metadata')->nullable()->after('parameters');
            $table->float('response_time')->nullable()->after('processing_time');
            $table->text('error_message')->nullable()->after('error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remover campos adicionados
            $table->dropColumn(['session_id', 'parameters', 'metadata', 'response_time', 'error_message']);
        });
    }
};
