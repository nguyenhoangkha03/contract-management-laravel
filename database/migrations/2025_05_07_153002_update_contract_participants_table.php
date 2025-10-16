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
        Schema::table('contract_participants', function (Blueprint $table) {
            $table->dropColumn('company_name')->nullable();
            $table->string('full_name');
            $table->dropForeign('contract_participants_representative_id_foreign');
            $table->dropColumn('representative_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
