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
        Schema::create('contract_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->enum('adjusted_field', ['start_date', 'end_date', 'total_value']);
            $table->string('old_value');
            $table->string('new_value');
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->timestamp('adjustment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_adjustments');
    }
};
