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
        Schema::create('navigation_menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')
                ->constrained('navigation_menu_groups')
                ->cascadeOnDelete();

            $table->string('title');

            $table->enum('link_type', [
                'collection',
                'cms_page',
                'category',
                'custom'
            ]);

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('custom_url')->nullable();

            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_menu_items');
    }
};
