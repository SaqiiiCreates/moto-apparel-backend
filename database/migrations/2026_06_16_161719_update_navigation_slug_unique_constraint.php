<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('navigation_menus', function (Blueprint $table) {
            $table->dropUnique('navigation_menus_slug_unique');

            $table->unique(['slug', 'menu_type']);
        });
    }

    public function down(): void
    {
        Schema::table('navigation_menus', function (Blueprint $table) {
            $table->dropUnique(['slug', 'menu_type']);
            $table->unique('slug');
        });
    }
};