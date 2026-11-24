<?php

use Illuminate\Support\Facades\Route;
use Webkul\Page\Http\Controllers\Admin\PageController;
use Webkul\Page\Http\Controllers\Admin\PageSectionController;

Route::group(['middleware' => ['web', 'admin']], function () {

    Route::prefix(config('app.admin_url'))->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Pages
        |--------------------------------------------------------------------------
        */
        Route::controller(PageController::class)
            ->prefix('pages')
            ->name('admin.pages.')
            ->group(function () {

                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('create', 'store')->name('store');

                Route::get('edit/{id}', 'edit')->name('edit');
                Route::put('edit/{id}', 'update')->name('update');

                Route::post('{id}/banner', 'updateBanner')->name('banner.update');

                Route::delete('delete/{id}', 'destroy')->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Page Sections
        |--------------------------------------------------------------------------
        */
        Route::controller(PageSectionController::class)
            ->prefix('pages/{page}/sections')
            ->name('admin.pages.sections.')
            ->group(function () {

                Route::post('/', 'store')->name('store');
                Route::put('{section}', 'update')->name('update');
                Route::delete('{section}', 'destroy')->name('destroy');

                // optional: reorder sections
                Route::post('sort', 'sort')->name('sort');
            });
    });

});