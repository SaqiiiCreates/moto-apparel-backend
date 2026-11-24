<?php

use Illuminate\Support\Facades\Route;
use Webkul\Page\Http\Controllers\API\PageController as ApiPageController;

// routes/api.php
Route::group(['prefix' => 'api/v1'], function () {

    /*
    |--------------------------------------------------------------------------
    | Pages API
    |--------------------------------------------------------------------------
    */

    // Get page by slug (frontend use-case)
    Route::get('pages/{slug}', [ApiPageController::class, 'show']);

});