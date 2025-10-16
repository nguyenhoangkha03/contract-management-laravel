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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_purpose')->nullable()->change();
            $table->string('contract_form')->nullable()->change();
            $table->string('pay_method')->nullable()->change();
            $table->text('payment_terms')->nullable()->change();
            $table->text('legal_basis')->nullable()->change();
            $table->text('payment_requirements')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
};
