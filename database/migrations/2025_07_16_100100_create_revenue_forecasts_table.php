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
        Schema::create('revenue_forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('forecast_date');
            $table->string('forecast_type')->default('monthly'); // monthly, quarterly, yearly
            $table->string('forecast_method')->default('linear'); // linear, exponential, seasonal
            
            // Forecast Data
            $table->decimal('predicted_revenue', 15, 2)->default(0);
            $table->decimal('actual_revenue', 15, 2)->nullable();
            $table->decimal('confidence_level', 5, 2)->default(0); // 0-100
            $table->decimal('accuracy_score', 5, 2)->nullable(); // 0-100
            
            // Trend Analysis
            $table->decimal('growth_rate', 5, 2)->default(0);
            $table->decimal('trend_coefficient', 10, 6)->default(0);
            $table->string('trend_direction')->default('stable'); // increasing, decreasing, stable
            
            // Seasonality
            $table->decimal('seasonal_factor', 5, 2)->default(1);
            $table->boolean('is_seasonal')->default(false);
            
            // Supporting Data
            $table->integer('active_contracts_count')->default(0);
            $table->integer('new_contracts_count')->default(0);
            $table->integer('completed_contracts_count')->default(0);
            $table->decimal('average_contract_value', 15, 2)->default(0);
            
            $table->json('forecast_factors')->nullable(); // Store additional factors
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['forecast_date', 'forecast_type']);
            $table->index('forecast_method');
            $table->unique(['forecast_date', 'forecast_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_forecasts');
    }
};