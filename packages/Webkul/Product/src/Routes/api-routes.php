<?php

use Illuminate\Support\Facades\Route;
use Webkul\Product\Http\Controllers\API\ProductController as ApiProductController;

// routes/api.php
Route::group(['prefix' => 'api/v1'], function () {    
    Route::get('corvit-products/home', [ApiProductController::class, 'latest']);
    Route::get('corvit-products/{slug}', [ApiProductController::class, 'show']);
});