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
        Schema::create('collection_tab_categories', function (Blueprint $table) {
            $table->id();

            // BIGINT -> collection_tabs.id
            $table->foreignId('collection_tab_id')
                ->constrained('collection_tabs')
                ->cascadeOnDelete();

            // INT -> categories.id
            $table->unsignedInteger('category_id');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['collection_tab_id', 'category_id']);
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_tab_categories');
    }
};
