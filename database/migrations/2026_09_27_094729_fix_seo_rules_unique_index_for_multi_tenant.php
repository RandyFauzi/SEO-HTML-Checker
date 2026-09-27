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
            // Drop the old global unique constraint on 'code'
            $table->dropUnique('seo_rules_code_unique');
            
            // Add a composite unique constraint so each user can have one rule per code
            $table->unique(['user_id', 'code'], 'seo_rules_user_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_rules', function (Blueprint $table) {
            $table->dropUnique('seo_rules_user_id_code_unique');
            $table->unique('code', 'seo_rules_code_unique');
        });
    }
};
