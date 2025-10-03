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
            // Adicionar campos de custo
            $table->decimal('cost_usd', 10, 6)->default(0.000000)->after('tokens_output');
            $table->decimal('cost_brl', 10, 4)->default(0.0000)->after('cost_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remover campos de custo
            $table->dropColumn(['cost_usd', 'cost_brl']);
        });
    }
};
