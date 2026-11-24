<?php

use Illuminate\Support\Facades\Route;
use Webkul\Slider\Http\Controllers\API\SliderController as ApiSliderController;

// routes/api.php
Route::group(['prefix' => 'api/v1'], function () {
    // Changed index to show/{slug} to match your controller
    Route::get('sliders/{slug}', [ApiSliderController::class, 'show']);
});