<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\NavigationController;

Route::group([
    'prefix' => 'navigation',
    'middleware' => ['web', 'admin'],
], function () {

    Route::get('/', [NavigationController::class, 'index'])
        ->name('admin.navigation.index');

    Route::get('/create', [NavigationController::class, 'create'])
        ->name('admin.navigation.create');

    Route::post('/store', [NavigationController::class, 'store'])
        ->name('admin.navigation.store');

    Route::get('/edit/{id}', [NavigationController::class, 'edit'])
        ->name('admin.navigation.edit');

    Route::post('/update/{id}', [NavigationController::class, 'update'])
        ->name('admin.navigation.update');

    Route::delete('/delete/{id}', [NavigationController::class, 'destroy'])
        ->name('admin.navigation.destroy');
});