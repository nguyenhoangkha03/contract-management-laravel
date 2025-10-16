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
        Schema::create('contract_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->date('period_date'); // Ngày tính toán metrics
            $table->string('period_type')->default('monthly'); // monthly, quarterly, yearly
            
            // Performance Metrics
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->decimal('payment_percentage', 5, 2)->default(0);
            
            // Timeline Metrics
            $table->integer('contract_duration_days')->default(0);
            $table->integer('days_since_start')->default(0);
            $table->integer('days_to_end')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            
            // Status Metrics
            $table->string('performance_status')->default('normal'); // excellent, good, normal, poor, critical
            $table->boolean('is_overdue')->default(false);
            $table->integer('overdue_days')->default(0);
            
            // Financial Health
            $table->decimal('monthly_revenue', 15, 2)->default(0);
            $table->decimal('projected_revenue', 15, 2)->default(0);
            $table->decimal('risk_score', 5, 2)->default(0); // 0-100
            
            // Client Metrics
            $table->integer('invoice_count')->default(0);
            $table->integer('payment_count')->default(0);
            $table->decimal('average_payment_days', 5, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['contract_id', 'period_date']);
            $table->index(['period_type', 'period_date']);
            $table->index('performance_status');
            $table->unique(['contract_id', 'period_date', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_analytics');
    }
};