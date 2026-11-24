<?php

use Illuminate\Support\Facades\Route;
use Webkul\Banner\Http\Controllers\Admin\BannerController;;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix(config('app.admin_url'))->group(function () {

        Route::controller(BannerController::class)->prefix('banners')->group(function () {
            Route::get('', 'index')->name('admin.banners.index');
            Route::get('create', 'create')->name('admin.banners.create');
            Route::post('create', 'store')->name('admin.banners.store');
            Route::get('edit/{id}', 'edit')->name('admin.banners.edit');
            Route::put('edit/{id}', 'update')->name('admin.banners.update');
            Route::delete('delete/{id}', 'destroy')->name('admin.banners.delete');
        });       
    });
});