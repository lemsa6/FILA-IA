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
            $table->foreignId('api_key_id')->constrained('api_keys')->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            $table->foreignId('request_id')->nullable()->constrained('requests')->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->text('prompt')->nullable();
            $table->text('response')->nullable();
            $table->bigInteger('tokens_input')->default(0);
            $table->bigInteger('tokens_output')->default(0);
            $table->bigInteger('total_tokens')->default(0);
            $table->string('model_used')->nullable();
            $table->decimal('cost_usd', 10, 6)->default(0.000000);
            $table->decimal('cost_brl', 10, 2)->default(0.00);
            $table->date('usage_date');
            $table->enum('usage_period', ['daily', 'monthly', 'yearly'])->default('daily');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->text('notes')->nullable();
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
