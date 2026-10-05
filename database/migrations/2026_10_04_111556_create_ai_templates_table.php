<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['LP', 'AMP']);
            $table->text('url_or_html');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_templates');
    }
};