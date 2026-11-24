<?php

use Webkul\Admin\Http\Controllers\API\TopBarController;
use Webkul\Admin\Http\Controllers\API\SearchSettingsController;
use Webkul\Admin\Http\Controllers\API\FooterLinksController;
use Webkul\Admin\Http\Controllers\API\DesignSettingsController;
use Webkul\Admin\Http\Controllers\API\SeoController;
use App\Http\Controllers\Api\CheckoutFooterLinkController;
use App\Http\Controllers\Api\EmptyCartCtaController;
use Illuminate\Support\Facades\Route;

Route::get('topbar-settings', [TopBarController::class, 'index']);

Route::get('search-settings', [SearchSettingsController::class, 'index']);

Route::get('footer-links', [FooterLinksController::class, 'index']);

Route::get('design', [DesignSettingsController::class, 'index']);

Route::get('seo', [SeoController::class, 'index']);

Route::get('cart/empty-ctas', [EmptyCartCtaController::class, 'index']);

Route::get('checkout/footer-links', [CheckoutFooterLinkController::class, 'index']);