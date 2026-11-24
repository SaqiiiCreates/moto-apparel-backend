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
        Schema::table('navigation_menus', function (Blueprint $table) {
            $table->text('bottom_text')->nullable();
            $table->string('bottom_link_label')->nullable();
            $table->string('bottom_link_url')->nullable();
            $table->text('image_subtitle')->nullable();
        });

        Schema::table('navigation_menu_items', function (Blueprint $table) {
            $table->string('display_style')->default('standard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigation_menu_items', function (Blueprint $table) {
            $table->dropColumn('display_style');
        });

        Schema::table('navigation_menus', function (Blueprint $table) {
            $table->dropColumn([
                'bottom_text',
                'bottom_link_label',
                'bottom_link_url',
                'image_subtitle',
            ]);
        });
    }
};
