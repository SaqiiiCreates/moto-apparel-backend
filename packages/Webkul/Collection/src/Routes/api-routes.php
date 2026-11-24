<?php

use Illuminate\Support\Facades\Route;
use Webkul\Collection\Http\Controllers\API\CollectionController as ApiCollectionController;

// routes/api.php
Route::group(['prefix' => 'api/v1'], function () {    
    Route::get('collections/popular', [ApiCollectionController::class, 'index']);
    Route::get('collections/{slug}', [ApiCollectionController::class, 'show']);
});