<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Payment\Facades\Payment;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Shop\Http\Requests\CartAddressRequest;
use Webkul\Shop\Http\Resources\CartResource;
use Illuminate\Support\Facades\Log;

class OnepageController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Return cart summary.
     */
    public function summary(): JsonResource
    {
        $cart = Cart::getCart();

        return new CartResource($cart);
    }

    /**
     * Store address.
     */
    public function storeAddress(CartAddressRequest $cartAddressRequest): JsonResource
    {
    // Log::info('ADDRESS DEBUG START', [
    //     'session_id' => session()->getId(),
    //     'session_cart_id' => session('cart'),
    //     'guest_token_header' => request()->header('X-Guest-Token'),
    //     'all_headers' => request()->headers->all(),
    // ]);

        $params = $cartAddressRequest->all();
        $cart = Cart::getCart();
        if (!$cart) {
            // Force init or create if missing
            $guestToken = request()->header('X-Guest-Token');
            if ($guestToken) {
                $cartRepo = app(\Webkul\Checkout\Repositories\CartRepository::class);
                $cart = $cartRepo->findOneWhere(['guest_token' => $guestToken, 'is_active' => 1]);
                if ($cart) Cart::setCart($cart);
            }
            if (!$cart) {
                return new JsonResource(['message' => 'Cart not found'], 404);
            }
        }

        if (
            ! auth()->guard('customer')->check()
            && ! Cart::getCart()->hasGuestCheckoutItems()
        ) {
            return new JsonResource([
                'redirect' => true,
                'data'     => route('shop.customer.session.index'),
            ]);
        }

        if (Cart::hasError()) {
            return new JsonResource([
                'redirect'     => true,
                'redirect_url' => route('shop.checkout.cart.index'),
            ]);
        }

        Cart::saveAddresses($params);

        $cart = Cart::getCart();

    // Log::info('ADDRESS DEBUG CART', [
    //     'cart_found' => (bool) $cart,
    //     'cart_id' => $cart?->id,
    // ]);
    if (! $cart) {
        // Log::error('ADDRESS CART NULL');

        return response()->json([
            'message' => 'Cart not found',
        ], 500);
    }
        Cart::collectTotals();

        if ($cart->haveStockableItems()) {
            if (! $rates = Shipping::collectRates()) {
                return new JsonResource([
                    'redirect'     => true,
                    'redirect_url' => route('shop.checkout.cart.index'),
                ]);
            }

            return new JsonResource([
                'redirect' => false,
                'data'     => $rates,
            ]);
        }

        return new JsonResource([
            'redirect' => false,
            'data'     => Payment::getSupportedPaymentMethods(),
        ]);
    }

    /**
     * Store shipping method.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeShippingMethod()
    {
        // Log::info('FULL AUTH DEBUG', [
        //     'guard_check' => auth()->guard('customer')->check(),
        //     'user' => auth()->guard('customer')->user(),
        //     'session_id' => session()->getId(),
        //     'session_all' => session()->all(),
        //     'cookies' => request()->cookies->all(),
        // ]);
        // Log::info('AUTH CHECK', ['logged_in' => auth()->guard('customer')->check(), 'user_id' => auth()->guard('customer')->id()]);
        try {
            // Log::info('SHIPPING METHOD REQUEST', request()->all());

            $cart = Cart::getCart();

            if (!$cart) {
                $guestToken = request()->header('X-Guest-Token');
                $customer   = auth()->guard('customer')->user();

                $cartRepo = app(\Webkul\Checkout\Repositories\CartRepository::class);

                if ($customer) {
                    $cart = $cartRepo->findOneWhere(['customer_id' => $customer->id, 'is_active' => 1]);
                } elseif ($guestToken) {
                    $cart = $cartRepo->findOneWhere(['guest_token' => $guestToken, 'is_active' => 1]);
                }

                if (!$cart && $guestToken) {
                    $cart = Cart::createCart(['guest_token' => $guestToken]);
                }

                if ($cart) {
                    Cart::setCart($cart);
                } else {
                    // Log::warning('CART FORCE FAILED', ['guest_token' => $guestToken]);
                    return response()->json(['redirect_url' => route('shop.checkout.cart.index')], 403);
                }
            }
            Cart::collectTotals();
            Cart::refreshCart();

            // Log::info('SHIPPING PRE-VALIDATE', [
            //     'cart_id' => $cart?->id,
            //     'has_error' => Cart::hasError(),
            // ]);

            $validatedData = $this->validate(request(), [
                'shipping_method' => 'required',
            ]);

            // Log::info('SHIPPING DEBUG', [
            //     'cart_id' => Cart::getCart()?->id,
            //     'has_error' => Cart::hasError(),
            //     'guest_token' => request()->header('X-Guest-Token'),
            //     'validated' => $validatedData,
            // ]);

            $result = Cart::saveShippingMethod(
                $validatedData['shipping_method']
            );

    // Log::info('SHIPPING METHOD RESULT', [
    //     'method' => $validatedData['shipping_method'],
    //     'result' => $result,
    //     'selected_rate' => Cart::getCart()?->selected_shipping_rate,
    // ]);

            if (
                Cart::hasError()
                || ! $validatedData['shipping_method']
                || ! $result
            ) {
                // Log::warning('SHIPPING HAS ERROR OR SAVE FAILED');
                return response()->json([
                    'redirect_url' => route('shop.checkout.cart.index'),
                ], 403);
            }

            Cart::collectTotals();

            return response()->json(
                Payment::getSupportedPaymentMethods()
            );
        } catch (\Exception $e) {
            Log::error('SHIPPING EXCEPTION', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['redirect_url' => route('shop.checkout.cart.index')], 403);
        }
    }

    /**
     * Store payment method.
     *
     * @return array
     */
    public function storePaymentMethod()
    {
        $cart = Cart::getCart();
        if (!$cart) {
            $guestToken = request()->header('X-Guest-Token');
            if ($guestToken) {
                $cartRepo = app(\Webkul\Checkout\Repositories\CartRepository::class);
                $cart = $cartRepo->findOneWhere(['guest_token' => $guestToken, 'is_active' => 1]);
                if ($cart) Cart::setCart($cart);
            }
            if (!$cart) {
                return response()->json(['redirect_url' => route('shop.checkout.cart.index')], 403);
            }
        }
        Cart::collectTotals();
        // Log::info('PAYMENT DEBUG', [
        //     'cart_id' => $cart?->id,
        //     'has_error' => Cart::hasError(),
        //     'payment' => $validatedData['payment'] ?? null,
        // ]);

        $validatedData = $this->validate(request(), [
            'payment' => 'required',
        ]);

        if (
            Cart::hasError()
            || ! $validatedData['payment']
            || ! Cart::savePaymentMethod($validatedData['payment'])
        ) {
            return response()->json([
                'redirect_url' => route('shop.checkout.cart.index'),
            ], Response::HTTP_FORBIDDEN);
        }

        Cart::collectTotals();

        $cart = Cart::getCart();

        return [
            'cart' => new CartResource($cart),
        ];
    }

    /**
     * Store order
     */
    public function storeOrder()
    {
        // Log::info('STORE ORDER START', [
        //     'cart_id' => Cart::getCart()?->id,
        //     'has_error' => Cart::hasError(),
        // ]);
        if (Cart::hasError()) {
            return new JsonResource([
                'redirect'     => true,
                'redirect_url' => route('shop.checkout.cart.index'),
            ]);
        }

        Cart::collectTotals();

        try {
            $this->validateOrder();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $cart = Cart::getCart();

        if ($redirectUrl = Payment::getRedirectUrl($cart)) {
            return new JsonResource([
                'redirect'     => true,
                'redirect_url' => $redirectUrl,
            ]);
        }

        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        Cart::deActivateCart();

        session()->flash('order_id', $order->id);

        return new JsonResource([
            'redirect'     => true,
            'redirect_url' => route('shop.checkout.onepage.success'),
        ]);
    }

    /**
     * Validate order before creation.
     *
     * @return void|\Exception
     */
    public function validateOrder()
    {
        $cart = Cart::getCart();
        // Log::info('VALIDATE ORDER', [
        //     'cart_id' => $cart?->id,
        //     'billing' => $cart?->billing_address ? true : false,
        //     'shipping' => $cart?->shipping_address ? true : false,
        //     'shipping_rate' => $cart?->selected_shipping_rate ? true : false,
        //     'payment' => $cart?->payment ? $cart->payment->method : false,
        // ]);

        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (
            auth()->guard('customer')->check()
            && auth()->guard('customer')->user()->is_suspended
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.suspended-account-message'));
        }

        if (
            auth()->guard('customer')->user()
            && ! auth()->guard('customer')->user()->status
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.inactive-account-message'));
        }

        if (! Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        if ($cart->haveStockableItems() && ! $cart->shipping_address) {
            throw new \Exception(trans('shop::app.checkout.onepage.address.check-shipping-address'));
        }

        if (! $cart->billing_address) {
            throw new \Exception(trans('shop::app.checkout.onepage.address.check-billing-address'));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->selected_shipping_rate
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-shipping-method'));
        }

        if (! $cart->payment) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-payment-method'));
        }
    }
}
