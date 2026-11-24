<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Webkul\Shop\Http\Requests\Customer\LoginRequest;
use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Str;

class CustomerController extends APIController
{
    /**
     * Login Customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        if (! auth()->guard('customer')->attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => trans('shop::app.customers.login-form.invalid-credentials'),
            ], Response::HTTP_FORBIDDEN);
        }

        $customer = auth()->guard('customer')->user();

        if (! $customer->status) {
            auth()->guard('customer')->logout();
            return response()->json([
                'message' => trans('shop::app.customers.login-form.not-activated'),
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $customer->is_verified) {
            Cookie::queue(Cookie::make('enable-resend', 'true', 1));
            Cookie::queue(Cookie::make('email-for-resend', $request->get('email'), 1));
            auth()->guard('customer')->logout();
            return response()->json([
                'message' => trans('shop::app.customers.login-form.verify-first'),
            ], Response::HTTP_FORBIDDEN);
        }

        session()->regenerate(true);
        auth()->guard('customer')->loginUsingId($customer->id);
        session()->put('customer_id', $customer->id);
        Event::dispatch('customer.after.login', $customer);

        return response()->json([]);
    }

    public function logout()
    {
        $customer = auth()->guard('customer')->user();
        $guestToken = null;

        // Convert customer cart to guest cart BEFORE logging out
        if ($customer) {
            $cart = Cart::getCart();

            if ($cart && $cart->customer_id == $customer->id) {
                $guestToken = $cart->guest_token ?: (string) \Illuminate\Support\Str::uuid();

                $cart->guest_token = $guestToken;
                $cart->customer_id = null;
                $cart->save();

                // Also update session so frontend can pick it immediately
                session()->put('guest_token', $guestToken);
            }
        }

        auth()->guard('customer')->logout();

        session()->forget([
            'customer_id',
            'login_customer_59ba36addc2b2f9401580f014c7f58ea4e30989d',
        ]);
        session()->regenerate(true);

        return response()->json([
            'message'     => 'Logged out successfully',
            'guest_token' => $guestToken,
        ]);
    }
}
