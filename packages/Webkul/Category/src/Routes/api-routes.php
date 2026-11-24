<?php

use Illuminate\Support\Facades\Route;
use Webkul\Category\Http\Controllers\API\CategoryShowController;

Route::middleware('api')->prefix('api')->group(function () {
    Route::get('corvit/category/{slug}', [CategoryShowController::class, 'show']);
});