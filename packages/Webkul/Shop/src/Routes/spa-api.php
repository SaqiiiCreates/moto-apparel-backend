<?php

use Illuminate\Support\Facades\Route;
use Webkul\Shop\Http\Controllers\API\Spa\CartController;

Route::prefix('spa')
    ->middleware(['web'])
    ->group(function () {

        Route::post('/guest-token', [CartController::class, 'guestToken']);
        
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/', [CartController::class, 'store']);
            Route::put('/', [CartController::class, 'update']);
            Route::delete('/{cart_item_id}', [CartController::class, 'destroy']);
            Route::post('/coupon', [CartController::class, 'applyCoupon']);    Route::delete('/coupon', [CartController::class, 'removeCoupon']);
        });

    });