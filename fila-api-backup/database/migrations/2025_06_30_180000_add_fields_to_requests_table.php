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
            // Renomear campos existentes para manter compatibilidade
            $table->renameColumn('prompt', 'content');
            $table->renameColumn('response', 'result');
            
            // Adicionar novos campos conforme documentação
            $table->integer('priority')->default(0)->after('status');
            $table->integer('attempts')->default(0)->after('priority');
            $table->text('error')->nullable()->after('result');
            $table->timestamp('completed_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remover novos campos
            $table->dropColumn(['priority', 'attempts', 'error', 'completed_at']);
            
            // Reverter renomeação
            $table->renameColumn('content', 'prompt');
            $table->renameColumn('result', 'response');
        });
    }
}; 