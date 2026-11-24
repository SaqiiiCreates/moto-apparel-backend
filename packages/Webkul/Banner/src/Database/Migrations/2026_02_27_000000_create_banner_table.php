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
        Schema::create('banner', function (Blueprint $table) {
            $table->increments('id');            
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('media_path')->nullable();
            $table->string('mobile_media_path')->nullable();
            $table->string('preheading')->nullable();
            $table->string('heading')->nullable();
            $table->text('description')->nullable();
            
            // JSON Fields
            $table->json('buttons')->nullable();
            $table->json('hotspots')->nullable();

            $table->boolean('is_active')->default(1);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner');
    }
};