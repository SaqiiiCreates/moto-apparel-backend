<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_tabs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collection_id')
                ->constrained('collections')
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('slug');

            $table->text('description')->nullable();

            $table->string('banner_image')->nullable();

            $table->string('thumbnail')->nullable();

            $table->longText('content')->nullable();

            $table->integer('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->string('type')->default('manual');
            
            $table->timestamps();

            $table->unique(['collection_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_tabs');
    }
};