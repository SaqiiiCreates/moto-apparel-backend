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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Create a PaymentIntent for a cart, or update an existing one
     * (payment_intent_id) to the cart's current total.
     *
     * The storefront calls this again right before confirming the payment,
     * after address and shipping are saved, so the amount charged equals
     * the final Bagisto grand total.
     */
    public function createIntent(Request $request)
    {
        $cartId = $request->input('cart_id');
        $paymentIntentId = $request->input('payment_intent_id');

        if (! $cartId) {
            return response()->json(['error' => 'cart_id required'], 422);
        }

        $cart = $this->freshCartTotals($cartId);

        if (! $cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        [$amount, $currency] = $this->cartAmount($cart);

        if ($amount <= 0) {
            return response()->json(['error' => 'Cart total is zero'], 422);
        }

        $secretKey = core()->getConfigData('sales.payment_methods.stripe.secret_key');

        if (! $secretKey) {
            return response()->json(['error' => 'Stripe secret key is not configured.'], 500);
        }

        \Stripe\Stripe::setApiKey($secretKey);

        try {
            if ($paymentIntentId) {
                $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

                if ((string) ($intent->metadata['cart_id'] ?? '') !== (string) $cart->id) {
                    return response()->json(['error' => 'Payment does not belong to this cart'], 422);
                }

                if (in_array($intent->status, ['succeeded', 'canceled', 'processing'], true)) {
                    return response()->json(['error' => 'Payment can no longer be updated'], 409);
                }

                if ($intent->amount !== $amount || $intent->currency !== $currency) {
                    $intent = \Stripe\PaymentIntent::update($intent->id, [
                        'amount'   => $amount,
                        'currency' => $currency,
                    ]);
                }
            } else {
                $intent = \Stripe\PaymentIntent::create([
                    'amount'                    => $amount,
                    'currency'                  => $currency,
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata'                  => ['cart_id' => $cart->id],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Stripe create/update intent failed', [
                'cart_id' => $cart->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Could not prepare the payment.'], 500);
        }

        return response()->json([
            'clientSecret' => $intent->client_secret,
            'amount'       => $intent->amount,
            'currency'     => $intent->currency,
        ]);
    }

    /**
     * Load a cart and recalculate its totals (shipping, tax, discounts).
     */
    protected function freshCartTotals($cartId)
    {
        $cart = app(CartRepository::class)->find($cartId);

        if (! $cart || ! $cart->is_active) {
            return null;
        }

        Cart::setCart($cart);
        Cart::collectTotals();

        return Cart::getCart()?->refresh() ?? $cart->refresh();
    }

    /**
     * Amount in the smallest currency unit, and the lowercase currency code.
     */
    protected function cartAmount($cart): array
    {
        $currency = strtolower($cart->cart_currency_code ?: 'USD');

        return [(int) round(((float) $cart->grand_total) * 100), $currency];
    }

    /**
     * The order already created for this PaymentIntent, if any.
     */
    protected function orderForPaymentIntent(string $paymentIntentId)
    {
        $orderId = DB::table('order_payment')
            ->where('method', 'stripe')
            ->where('additional', 'like', '%' . $paymentIntentId . '%')
            ->value('order_id');

        return $orderId ? $this->orderRepository->find($orderId) : null;
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

        // One order per PaymentIntent, even if the shopper double-clicks or retries.
        $lock = Cache::lock('stripe_confirm_' . $paymentIntentId, 60);

        if (! $lock->block(20)) {
            return response()->json(['error' => 'Payment is already being processed.'], 409);
        }

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'error' => 'Payment not succeeded',
                ], 400);
            }

            if ($existingOrder = $this->orderForPaymentIntent($paymentIntent->id)) {
                return response()->json([
                    'success'  => true,
                    'order_id' => $existingOrder->id,
                ]);
            }

            if ((string) ($paymentIntent->metadata['cart_id'] ?? '') !== (string) $cartId) {
                Log::warning('Stripe confirm: payment intent / cart mismatch', [
                    'payment_intent' => $paymentIntent->id,
                    'intent_cart_id' => $paymentIntent->metadata['cart_id'] ?? null,
                    'cart_id'        => $cartId,
                ]);

                return response()->json(['error' => 'Payment does not belong to this cart.'], 422);
            }

            request()->merge([
                'stripe_payment_intent' => $paymentIntent->toArray(),
                // Stored on order_payment.additional; used to find the order on retries.
                'orderData' => [
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ],
            ]);

            $cartRepo = app(CartRepository::class);
            $cart = $cartRepo->find($cartId);

            if (!$cart || ! $cart->is_active) {
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

            /*
            |--------------------------------------------------------------------------
            | Verify the charged amount matches the final cart total
            |--------------------------------------------------------------------------
            */

            Cart::setCart($cart);
            Cart::collectTotals();

            $cart = Cart::getCart();
            $cart->refresh();
            $cart->load(['payment', 'billing_address', 'shipping_address', 'shipping_rates', 'items']);

            [$expectedAmount, $expectedCurrency] = $this->cartAmount($cart);

            if ($paymentIntent->amount_received !== $expectedAmount || $paymentIntent->currency !== $expectedCurrency) {
                Log::error('Stripe confirm: amount mismatch, order not created', [
                    'payment_intent'  => $paymentIntent->id,
                    'cart_id'         => $cart->id,
                    'paid'            => $paymentIntent->amount_received,
                    'paid_currency'   => $paymentIntent->currency,
                    'expected'        => $expectedAmount,
                    'expected_currency' => $expectedCurrency,
                ]);

                return response()->json([
                    'error' => 'The amount paid does not match your order total. Please contact support; you will not be charged twice.',
                ], 409);
            }

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
            Log::error('Stripe confirm error', [
                'payment_intent' => $paymentIntentId,
                'cart_id'        => $cartId,
                'error'          => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            $lock->release();
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