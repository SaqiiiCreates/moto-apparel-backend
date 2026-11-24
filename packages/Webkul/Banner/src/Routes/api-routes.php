<?php

use Illuminate\Support\Facades\Route;
use Webkul\Banner\Http\Controllers\API\BannerController as ApiBannerController;

// routes/api.php
Route::group(['prefix' => 'api/v1'], function () {
    // Changed index to show/{slug} to match your controller
    Route::get('banners/{slug}', [ApiBannerController::class, 'show']);
});