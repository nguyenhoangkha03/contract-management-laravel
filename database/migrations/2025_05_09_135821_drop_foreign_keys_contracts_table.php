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
            $table->dropForeign('contracts_accountant_id_foreign');
            $table->dropColumn('accountant_id');

            $table->dropForeign('contracts_implementer_id_foreign');
            $table->dropColumn('implementer_id');

            $table->dropForeign('contracts_manager_id_foreign');
            $table->dropColumn('manager_id');

            $table->dropForeign('contracts_salesperson_id_foreign');
            $table->dropColumn('salesperson_id');
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
