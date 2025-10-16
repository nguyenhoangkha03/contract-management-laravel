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
        Schema::create('contract_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->enum('party_type', ['A', 'B', 'C']);
            $table->string('company_name');
            $table->string('address');
            $table->string('tax_code');
            $table->foreignId('representative_id')->constrained('clients')->onDelete('cascade');
            $table->string('representative_position');
            $table->string('phone');
            $table->string('bank_account');
            $table->string('bank_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_participants');
    }
};
