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
        Schema::table('invoices', function (Blueprint $table) {
            // Sửa enum status cho phù hợp với form
            $table->string('status')->change();
        });
        
        // Update existing records nếu cần
        \DB::statement("UPDATE invoices SET status = 'unpaid' WHERE status = 'nonexport'");
        \DB::statement("UPDATE invoices SET status = 'paid' WHERE status = 'exported'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['exported', 'nonexport'])->default('nonexport')->change();
        });
    }
};