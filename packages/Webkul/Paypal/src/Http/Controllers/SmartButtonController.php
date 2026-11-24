<?php

namespace Webkul\Paypal\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Paypal\Payment\SmartButton;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class SmartButtonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected SmartButton $smartButton,
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}
    
    /**
     * Paypal order creation for approval of client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder()
    {
        try {
            $cartId = request()->input('cart_id');
            // \Log::info('PayPal createOrder DEBUG', [
            //     'received_cart_id' => $cartId,
            //     'all_input' => request()->all(),
            //     'session_id' => session()->getId()
            // ]);
            
            $cartRepository = app(\Webkul\Checkout\Repositories\CartRepository::class);
            $cart = $cartRepository->find($cartId);
            // \Log::info('CREATE ORDER CART STATE', [
            //     'cart_id' => $cart->id,
            //     'shipping_rate' => $cart->selected_shipping_rate,
            //     'payment' => $cart->payment,
            // ]);
            if (!$cart) return response()->json(['message' => 'Cart not found'], 404);

                Cart::setCart($cart);
                Cart::collectTotals();

            // store cart id for capture step (important)
            session(['paypal_cart_id' => $cartId]);
            
            // \Log::info('CREATE COOKIE DEBUG', [
            //     'cookies' => request()->cookies->all(),
            // ]);
            session()->save();

            // \Log::info('CREATE SESSION DATA', [
            //     'session_id' => session()->getId(),
            //     'session_data' => session()->all(),
            // ]);

            // \Log::info('PAYPAL GUEST TOKEN', [
            //     'guest_token' => request()->header('X-Guest-Token'),
            // ]);
            $response = $this->smartButton->createOrder(
                $this->buildRequestBody($cart)
            );

            // \Log::info('PAYPAL RESPONSE', [
            //     'statusCode' => $response->statusCode ?? null,
            //     'result' => $response->result ?? null,
            // ]);
            return response()->json([
                'id' => $response->result->id ?? null,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Capturing paypal order after approval.
     *
     * @return \Illuminate\Http\Response
     */
    public function captureOrder()
    {
        // \Log::info('CAPTURE DEBUG', [
        //     'session_id' => session()->getId(),
        //     'session_data' => session()->all(),
        // ]);
        try {
            $this->smartButton->captureOrder(request()->input('orderData.orderID'));
            // \Log::info('CAPTURE COOKIE DEBUG', [
            //     'cookies' => request()->cookies->all(),
            // ]);
            $cartId = session('paypal_cart_id');
                if ($cartId) {
                    $cart = app(\Webkul\Checkout\Repositories\CartRepository::class)->find($cartId);
                    if ($cart) Cart::setCart($cart);
                }
                Cart::collectTotals();
            $result = $this->saveOrder();
            // \Log::info('PAYPAL GUEST TOKEN', [
            //     'guest_token' => request()->header('X-Guest-Token'),
            // ]);
            return $result instanceof \Illuminate\Http\JsonResponse
                ? $result
                : response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build request body.
     *
     * @return array
     */
    protected function buildRequestBody($cart)
    {
        $billing = $cart->billing_address;

        // Fallback if no billing address
        if (!$billing) {
            \Log::warning('PayPal: No billing_address found on cart');
            
            $billing = (object) [
                'first_name' => 'Guest',
                'last_name'  => 'User',
                'address'    => 'Dummy Street 123',
                'city'       => 'Karachi',
                'state'      => 'Sindh',
                'postcode'   => '75000',
                'country'    => 'PK',
                'email'      => 'guest@example.com',
                'phone'      => '',
            ];
        }

        $billingAddressLines = $this->getAddressLines($billing->address ?? '');

        $data = [
            'intent' => 'CAPTURE',

            'payer' => [
                'name' => [
                    'given_name' => $billing->first_name ?? 'Guest',
                    'surname'    => $billing->last_name ?? 'User',
                ],

                'address' => [
                    'address_line_1' => current($billingAddressLines),
                    'address_line_2' => last($billingAddressLines),
                    'admin_area_2'   => $billing->city ?? '',
                    'admin_area_1'   => $billing->state ?? '',
                    'postal_code'    => $billing->postcode ?? '',
                    'country_code'   => $billing->country ?? 'PK',
                ],

                'email_address' => $billing->email ?? 'guest@example.com',
            ],

            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
            ],

            'purchase_units' => [
                [
                    'amount' => [
                        'value' => $this->smartButton->formatCurrencyValue(
                            (float) $cart->sub_total + $cart->tax_total 
                            + ($cart->selected_shipping_rate?->price ?? 0) 
                            - $cart->discount_amount
                        ),
                        'currency_code' => $cart->cart_currency_code,

                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $this->smartButton->formatCurrencyValue((float) $cart->sub_total),
                            ],
                            'shipping' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $this->smartButton->formatCurrencyValue((float) ($cart->selected_shipping_rate?->price ?? 0)),
                            ],
                            'tax_total' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $this->smartButton->formatCurrencyValue((float) $cart->tax_total),
                            ],
                            'discount' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $this->smartButton->formatCurrencyValue((float) $cart->discount_amount),
                            ],
                        ],
                    ],

                    'items' => $this->getLineItems($cart),
                ],
            ],
        ];

        // Phone (optional)
        if (!empty($billing->phone)) {
            $data['payer']['phone'] = [
                'phone_type' => 'MOBILE',
                'phone_number' => [
                    'national_number' => $this->smartButton->formatPhone($billing->phone),
                ],
            ];
        }

        // Shipping (if needed)
        if ($cart->haveStockableItems() && $cart->shipping_address) {
            $data['application_context']['shipping_preference'] = 'SET_PROVIDED_ADDRESS';

            $shippingAddressLines = $this->getAddressLines($cart->shipping_address->address ?? '');

            $data['purchase_units'][0]['shipping'] = [
                'address' => [
                    'address_line_1' => current($shippingAddressLines),
                    'address_line_2' => last($shippingAddressLines),
                    'admin_area_2'   => $cart->shipping_address->city ?? '',
                    'admin_area_1'   => $cart->shipping_address->state ?? '',
                    'postal_code'    => $cart->shipping_address->postcode ?? '',
                    'country_code'   => $cart->shipping_address->country ?? 'PK',
                ],
            ];
        }

        return $data;
    }

    /**
     * Return cart items.
     *
     * @param  string  $cart
     * @return array
     */
    protected function getLineItems($cart)
    {
        $lineItems = [];

        foreach ($cart->items as $item) {
            $lineItems[] = [
                'unit_amount' => [
                    'currency_code' => $cart->cart_currency_code,
                    'value'         => $this->smartButton->formatCurrencyValue((float) $item->price),
                ],
                'quantity'    => $item->quantity,
                'name'        => $item->name,
                'sku'         => $item->sku,
                'category'    => $item->getTypeInstance()->isStockable() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS',
            ];
        }

        return $lineItems;
    }

    /**
     * Return convert multiple address lines into 2 address lines.
     *
     * @param  string  $address
     * @return array
     */
    protected function getAddressLines($address)
    {
        $address = explode(PHP_EOL, $address, 2);

        $addressLines = [current($address)];

        if (isset($address[1])) {
            $addressLines[] = str_replace(["\r\n", "\r", "\n"], ' ', last($address));
        } else {
            $addressLines[] = '';
        }

        return $addressLines;
    }

    /**
     * Saving order once captured and all formalities done.
     *
     * @return \Illuminate\Http\Response
     */
    protected function saveOrder()
    {
        // \Log::info('SAVE ORDER DEBUG', [
        //     'session_id' => session()->getId(),
        //     'paypal_cart_id' => session('paypal_cart_id'),
        //     'cart_id' => Cart::getCart()?->id,
        //     'has_error' => Cart::hasError(),
        // ]);
        if (Cart::hasError()) {
            return response()->json(['redirect_url' => route('shop.checkout.cart.index')], 403);
        }

        try {
            Cart::collectTotals();

            $this->validateOrder();

            $cart = Cart::getCart();

            $data = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($data);

            $this->orderRepository->update(['status' => 'processing'], $order->id);

            if ($order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData($order));
            }

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            session()->flash('error', trans('shop::app.common.error'));

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepares order's invoice data for creation.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    /**
     * Validate order before creation.
     *
     * @return void|\Exception
     */
    protected function validateOrder()
    {
        //\Log::info('VALIDATE ORDER ENTERED');
        $cart = Cart::getCart();

        // logger('SHIPPING RATE DEBUG', [
        //     'shipping_rate' => $cart->selected_shipping_rate,
        //     'payment'       => $cart->payment,
        // ]);
        if (! $cart) {
                throw new \Exception('Cart not found');
            }

        // \Log::info('PAYPAL VALIDATE ORDER', [
        //     'cart_id' => $cart->id,
        //     'billing_address' => !empty($cart?->billing_address),
        //     'shipping_address' => !empty($cart?->shipping_address),
        //     'selected_shipping_rate' => !empty($cart?->selected_shipping_rate),
        //     'payment' => !empty($cart?->payment),
        //     'has_stockable_items' => $cart?->haveStockableItems(),
        // ]);

        $minimumOrderAmount = (float) core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (! Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->shipping_address
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.check-shipping-address'));
        }

        if (! $cart->billing_address) {
            throw new \Exception(trans('shop::app.checkout.cart.check-billing-address'));
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
