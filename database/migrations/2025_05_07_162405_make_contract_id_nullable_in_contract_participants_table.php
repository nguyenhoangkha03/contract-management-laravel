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
            $table->dropForeign('contract_participants_contract_id_foreign');
            $table->dropColumn('contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_participants', function (Blueprint $table) {
            //
        });
    }
};
