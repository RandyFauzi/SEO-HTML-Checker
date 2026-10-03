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
        Schema::table('seo_rules', function (Blueprint $table) {
            $table->enum('target_page', ['all', 'lp', 'amp'])->default('all')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_rules', function (Blueprint $table) {
            $table->dropColumn('target_page');
        });
    }
};
