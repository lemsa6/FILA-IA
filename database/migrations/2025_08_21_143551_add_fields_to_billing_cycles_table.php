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
        Schema::table('billing_cycles', function (Blueprint $table) {
            $table->date('cycle_start_date')->nullable();
            $table->date('cycle_end_date')->nullable();
            $table->enum('cycle_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->bigInteger('total_input_tokens')->default(0);
            $table->bigInteger('total_output_tokens')->default(0);
            $table->integer('total_requests')->default(0);
            $table->decimal('base_plan_cost', 10, 2)->default(0.00);
            $table->decimal('excess_cost', 10, 2)->default(0.00);
            $table->decimal('total_cost', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'closed', 'billed', 'paid'])->default('active');
            $table->timestamp('billed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('billing_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_cycles', function (Blueprint $table) {
            $table->dropColumn([
                'cycle_start_date',
                'cycle_end_date',
                'cycle_type',
                'total_input_tokens',
                'total_output_tokens',
                'total_requests',
                'base_plan_cost',
                'excess_cost',
                'total_cost',
                'status',
                'billed_at',
                'paid_at',
                'invoice_number',
                'billing_notes'
            ]);
        });
    }
};
