<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NavigationApiController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\CheckoutController;

Route::get('/navigation', [NavigationApiController::class, 'index']);
Route::get('/search', [SearchController::class, 'search']);

Route::prefix('v1')->group(function () {

    // Stripe routes (no web middleware needed)
    Route::post('stripe/create-intent', [StripeController::class, 'createIntent']);
    Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);
    Route::post('stripe/confirm-payment', [StripeController::class, 'confirmPayment']);

    // Checkout routes - only these need web + AuthenticateSession
    Route::middleware(['web', \Laravel\Sanctum\Http\Middleware\AuthenticateSession::class])->group(function () {
        Route::post('checkout/addresses', [CheckoutController::class, 'storeAddress']);
        Route::post('checkout/shipping-methods', [CheckoutController::class, 'storeShippingMethod']);
        Route::post('checkout/payment-methods', [CheckoutController::class, 'storePaymentMethod']);
        Route::post('checkout/orders', [CheckoutController::class, 'storeOrder']);
    });

    // Config routes (no middleware needed)
    Route::get('paypal/config', function () {
        return response()->json([
            'client_id' => core()->getConfigData('sales.payment_methods.paypal_smart_button.client_id'),
            'currency'  => explode(',', core()->getConfigData('sales.payment_methods.paypal_smart_button.accepted_currencies') ?: 'USD')[0],
            'sandbox'   => (bool) core()->getConfigData('sales.payment_methods.paypal_smart_button.sandbox'),
            'active'    => (bool) core()->getConfigData('sales.payment_methods.paypal_smart_button.active'),
        ]);
    });

    Route::get('stripe/config', function () {
        return response()->json([
            'publishable_key' => core()->getConfigData('sales.payment_methods.stripe.publishable_key'),
            'currency'        => explode(',', core()->getConfigData('sales.payment_methods.stripe.accepted_currencies') ?: 'USD')[0],
            'sandbox'         => (bool) core()->getConfigData('sales.payment_methods.stripe.sandbox'),
            'active'          => (bool) core()->getConfigData('sales.payment_methods.stripe.active'),
        ]);
    });
});