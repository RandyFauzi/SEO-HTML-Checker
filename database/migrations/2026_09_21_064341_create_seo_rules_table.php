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
        Schema::create('seo_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general');

            $table->string('target_selector')->nullable();
            $table->string('attribute')->nullable();
            $table->string('operator')->nullable(); // '=', '!=', 'contains', 'between', '>', '<'
            $table->string('expected_value')->nullable();

            $table->string('rule_type'); // 'exist', 'count', 'length', 'text_match', 'regex', 'attribute', 'json_ld', 'compare_amp'

            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->string('regex_pattern')->nullable();

            $table->string('severity')->default('warning');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('version')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_rules');
    }
};
