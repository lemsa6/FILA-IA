<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adiciona coluna real para rastrear cache hits.
     * Antes disso, o dashboard tentava ler uma coluna `cache_info` que nunca
     * existiu na tabela, causando exceção silenciosa e zerando várias métricas.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->boolean('cache_hit')->default(false)->after('result');
            $table->index(['status', 'cache_hit']);
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'cache_hit']);
            $table->dropColumn('cache_hit');
        });
    }
};
