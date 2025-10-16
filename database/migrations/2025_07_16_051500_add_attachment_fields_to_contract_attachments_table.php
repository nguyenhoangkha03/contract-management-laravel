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
        Schema::table('contract_attachments', function (Blueprint $table) {
            $table->string('title')->nullable()->after('file_path');
            $table->enum('type', ['contract', 'appendix', 'annex', 'report', 'other'])->default('other')->after('title');
            $table->text('description')->nullable()->after('type');
            $table->timestamp('uploaded_at')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_attachments', function (Blueprint $table) {
            $table->dropColumn(['title', 'type', 'description', 'uploaded_at']);
        });
    }
};