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
        Schema::table('clients', function (Blueprint $table) {
            // Chỉ thêm column nếu chưa tồn tại
            if (!Schema::hasColumn('clients', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (!Schema::hasColumn('clients', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }
            if (!Schema::hasColumn('clients', 'remember_token')) {
                $table->rememberToken()->after('email_verified_at');
            }
            if (!Schema::hasColumn('clients', 'company_name')) {
                $table->string('company_name')->nullable()->after('address');
            }
            if (!Schema::hasColumn('clients', 'tax_code')) {
                $table->string('tax_code')->nullable()->after('company_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['password', 'email_verified_at', 'remember_token', 'company_name', 'tax_code']);
        });
    }
};