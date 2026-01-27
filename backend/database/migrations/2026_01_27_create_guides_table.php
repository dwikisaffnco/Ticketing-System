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
        Schema::create('guide_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('guide_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('problem');
            $table->json('solutions');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
        Schema::dropIfExists('guide_categories');
    }
};
