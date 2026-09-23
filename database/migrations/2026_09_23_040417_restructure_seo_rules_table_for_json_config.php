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
            // Drop flat columns
            $table->dropColumn([
                'target_selector', 
                'attribute', 
                'operator', 
                'expected_value', 
                'min_value', 
                'max_value', 
                'regex_pattern'
            ]);

            // Add new structured columns
            $table->json('config')->nullable()->after('rule_type');
            $table->text('issue_message')->nullable()->after('config');
            $table->text('reason_template')->nullable()->after('issue_message');
            $table->text('recommendation')->nullable()->after('reason_template');
            
            // Note: category, severity, priority, code, name, description, is_active, version, sort_order are already there, or we keep them.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_rules', function (Blueprint $table) {
            $table->dropColumn([
                'config',
                'issue_message',
                'reason_template',
                'recommendation'
            ]);

            $table->string('target_selector')->nullable();
            $table->string('attribute')->nullable();
            $table->string('operator')->nullable();
            $table->string('expected_value')->nullable();
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->string('regex_pattern')->nullable();
        });
    }
};
