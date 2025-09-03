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
        Schema::table('token_usage_logs', function (Blueprint $table) {
            // Verificar se os campos já existem antes de adicionar
            if (!Schema::hasColumn('token_usage_logs', 'api_key_id')) {
                $table->foreignId('api_key_id')->constrained('api_keys')->onDelete('cascade');
            }
            if (!Schema::hasColumn('token_usage_logs', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            }
            if (!Schema::hasColumn('token_usage_logs', 'request_id')) {
                $table->foreignId('request_id')->nullable()->constrained('requests')->onDelete('set null');
            }
            if (!Schema::hasColumn('token_usage_logs', 'session_id')) {
                $table->string('session_id')->nullable();
            }
            if (!Schema::hasColumn('token_usage_logs', 'prompt')) {
                $table->text('prompt')->nullable();
            }
            if (!Schema::hasColumn('token_usage_logs', 'response')) {
                $table->text('response')->nullable();
            }
            if (!Schema::hasColumn('token_usage_logs', 'tokens_input')) {
                $table->bigInteger('tokens_input')->default(0);
            }
            if (!Schema::hasColumn('token_usage_logs', 'tokens_output')) {
                $table->bigInteger('tokens_output')->default(0);
            }
            if (!Schema::hasColumn('token_usage_logs', 'total_tokens')) {
                $table->bigInteger('total_tokens')->default(0);
            }
            if (!Schema::hasColumn('token_usage_logs', 'model_used')) {
                $table->string('model_used')->nullable();
            }
            if (!Schema::hasColumn('token_usage_logs', 'cost_usd')) {
                $table->decimal('cost_usd', 10, 6)->default(0.000000);
            }
            if (!Schema::hasColumn('token_usage_logs', 'cost_brl')) {
                $table->decimal('cost_brl', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('token_usage_logs', 'usage_date')) {
                $table->date('usage_date');
            }
            if (!Schema::hasColumn('token_usage_logs', 'usage_period')) {
                $table->enum('usage_period', ['daily', 'monthly', 'yearly'])->default('daily');
            }
            if (!Schema::hasColumn('token_usage_logs', 'status')) {
                $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            }
            if (!Schema::hasColumn('token_usage_logs', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('token_usage_logs', function (Blueprint $table) {
            $table->dropForeign(['api_key_id', 'plan_id', 'request_id']);
            $table->dropColumn([
                'api_key_id',
                'plan_id',
                'request_id',
                'session_id',
                'prompt',
                'response',
                'tokens_input',
                'tokens_output',
                'total_tokens',
                'model_used',
                'cost_usd',
                'cost_brl',
                'usage_date',
                'usage_period',
                'status',
                'notes'
            ]);
        });
    }
};
