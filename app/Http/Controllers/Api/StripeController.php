<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\StripeService;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Sales\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\Cache;

class StripeController
{
    protected $orderRepository;
    protected $invoiceRepository;

    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function createIntent(Request $request)
    {
        $cartId = $request->cart_id;
        if (!$cartId) return response()->json(['error' => 'cart_id required'], 422);

        // Use cached cart if possible
        $cart = Cache::remember("cart_{$cartId}", 30, function() use ($cartId) {
            return app(CartRepository::class)->find($cartId);
        });

        if (!$cart) return response()->json(['error' => 'Cart not found'], 404);

        Cart::collectTotals();

        $amount = (int) round($cart->grand_total * 100);

        $secretKey = Cache::remember('stripe_secret', 3600, fn() => 
            core()->getConfigData('sales.payment_methods.stripe.secret_key')
        );

        \Stripe\Stripe::setApiKey($secretKey);

        $intent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => ['cart_id' => $cartId],
        ]);

        return response()->json(['clientSecret' => $intent->client_secret]);
    }

    public function confirmPayment(Request $request)
    {
        $data = $request->json()->all();
        $paymentIntentId = $data['payment_intent_id'] ?? null;
        $cartId = $data['cart_id'] ?? null;

        // \Log::info('Stripe Confirm Request', $data);

        if (!$paymentIntentId || !$cartId) {
            return response()->json(['error' => 'Missing parameters'], 422);
        }

        $key = core()->getConfigData('sales.payment_methods.stripe.secret_key');

        if (!$key) {
            return response()->json([
                'error' => 'Stripe secret key is not configured.',
            ], 500);
        }

        \Stripe\Stripe::setApiKey($key);

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'error' => 'Payment not succeeded',
                ], 400);
            }

            request()->merge([
                'stripe_payment_intent' => $paymentIntent->toArray(),
            ]);

            $cartRepo = app(CartRepository::class);
            $cart = $cartRepo->find($cartId);

            if (!$cart) {
                return response()->json([
                    'error' => 'Cart not found',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Ensure Stripe payment exists on the cart
            |--------------------------------------------------------------------------
            */

            if (!$cart->payment) {
                $cart->payment()->create([
                    'method' => 'stripe',
                ]);

                $cart->refresh();
                $cart->load([
                    'payment',
                    'billing_address',
                    'shipping_address',
                    'items',
                ]);
            }

            if (!$cart->payment) {
                return response()->json([
                    'error' => 'Unable to attach Stripe payment to cart',
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Ensure shipping rate is selected
            |--------------------------------------------------------------------------
            */

            Cart::setCart($cart);

            $cart->refresh();
            $cart->load([
                'payment',
                'billing_address',
                'shipping_address',
                'shipping_rates',
                'items',
            ]);

            if ($cart->haveStockableItems() && ! $cart->shipping_address) {
                return response()->json([
                    'error' => 'Shipping address is missing from cart',
                    'cart_id' => $cart->id,
                    'billing_address_id' => $cart->billing_address?->id,
                    'shipping_address_id' => $cart->shipping_address?->id,
                ], 422);
            }

            if ($cart->haveStockableItems() && ! $cart->selected_shipping_rate) {
                Cart::collectTotals();

                $rates = Shipping::collectRates();

                if (! $rates) {
                    return response()->json([
                        'error' => 'Unable to collect shipping rates',
                    ], 422);
                }

                $cart->refresh();
                $cart->load('shipping_rates');

                $shippingRate = $cart->shipping_rates()->first();

                if (! $shippingRate) {
                    return response()->json([
                        'error' => 'No shipping rate available after collecting rates',
                    ], 422);
                }

                $saved = Cart::saveShippingMethod($shippingRate->method);

                if (! $saved) {
                    return response()->json([
                        'error' => 'Unable to save shipping method',
                        'shipping_method' => $shippingRate->method,
                    ], 422);
                }

                Cart::collectTotals();

                $cart = Cart::getCart();

                $cart->refresh();
                $cart->load([
                    'payment',
                    'billing_address',
                    'shipping_address',
                    'shipping_rates',
                    'items',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */

            $data = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($data);

            if ($order) {

                $this->orderRepository->update([
                    'status' => 'processing',
                ], $order->id);

                if ($order->canInvoice()) {
                    $this->invoiceRepository->create(
                        $this->prepareInvoiceData($order)
                    );
                }

                Cart::deActivateCart();

                return response()->json([
                    'success'  => true,
                    'order_id' => $order->id,
                ]);
            }

            // \Log::error('createFromCart failed');
            return response()->json(['error' => 'Failed to create order'], 500);

        } catch (\Exception $e) {
            // \Log::error('Stripe confirm error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Prepare invoice data.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = [
            'order_id' => $order->id,
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}