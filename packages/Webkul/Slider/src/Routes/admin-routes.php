<?php

use Illuminate\Support\Facades\Route;
use Webkul\Slider\Http\Controllers\Admin\SliderController;
use Webkul\Slider\Http\Controllers\Admin\SlideController;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix(config('app.admin_url'))->group(function () {

        Route::controller(SliderController::class)->prefix('sliders')->group(function () {
            Route::get('', 'index')->name('admin.slider.index');
            Route::get('create', 'create')->name('admin.slider.create');
            Route::post('create', 'store')->name('admin.slider.store');
            Route::get('edit/{id}', 'edit')->name('admin.slider.edit');
            Route::put('edit/{id}', 'update')->name('admin.slider.update');
            Route::delete('delete/{id}', 'destroy')->name('admin.slider.delete');
        });
        Route::prefix('{slider_id}/slides')->group(function () {
            Route::get('create', [SlideController::class, 'create'])->name('admin.slider.slide.create');
            Route::post('create', [SlideController::class, 'store'])->name('admin.slider.slide.store');
            Route::get('edit/{id}', [SlideController::class, 'edit'])->name('admin.slider.slide.edit');
            Route::put('edit/{id}', [SlideController::class, 'update'])->name('admin.slider.slide.update');
            Route::delete('delete/{id}', [SlideController::class, 'destroy'])->name('admin.slider.slide.delete');
        });
    });
});