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
        Schema::create('navigation_menu_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('navigation_menus')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('link')->nullable();
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
        Schema::dropIfExists('navigation_menu_groups');
    }
};
