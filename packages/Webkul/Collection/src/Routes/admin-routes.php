<?php

use Illuminate\Support\Facades\Route;
use Webkul\Collection\Http\Controllers\Admin\CollectionController;
use Webkul\Collection\Http\Controllers\Admin\CollectionTabController;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix(config('app.admin_url'))->group(function () {

        Route::controller(CollectionController::class)->prefix('collections')->group(function () {
            Route::get('', 'index')->name('admin.collection.index');
            Route::get('create', 'create')->name('admin.collection.create');
            Route::post('create', 'store')->name('admin.collection.store');
            Route::get('edit/{id}', 'edit')->name('admin.collection.edit');
            Route::put('{id}', 'update')->name('admin.collection.update');
            Route::delete('delete/{id}', 'destroy')->name('admin.collection.delete');
        });

        Route::controller(CollectionTabController::class)
        ->prefix('collections/{collection}/tabs')
        ->group(function () {

            Route::post('', 'store')
                ->name('admin.collection.tabs.store');

            Route::put('{id}', 'update')
                ->name('admin.collection.tabs.update');

            Route::delete('{id}', 'destroy')
                ->name('admin.collection.tabs.delete');
        });
    });
});