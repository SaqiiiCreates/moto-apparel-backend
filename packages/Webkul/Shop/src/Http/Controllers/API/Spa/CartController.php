<?php

namespace Webkul\Shop\Http\Controllers\API\Spa;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Attribute\Models\AttributeOption;

class CartController
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CartRuleCouponRepository $cartRuleCouponRepository,
    ) {}

    public function guestToken()
    {
        $token = request()->cookie('guest_token');

        if (! $token) {
            $token = (string) Str::uuid();
        }

        return response()->json([
            'guest_token' => $token,
        ])->cookie(
            'guest_token',
            $token,
            60 * 24 * 30 // 30 days
        );
    }
    /*
    |----------------------------------------
    | GET CART
    |----------------------------------------
    */
    public function index(Request $request)
    {

        // Log::info('CART DEBUG (INDEX - START)', [
        //     'session_id' => session()->getId(),
        //     'session_has_cart' => Cart::getCart() ? true : false,
        //     'cart_id_direct' => Cart::getCart() ? Cart::getCart()->id : null,
        //     'request_headers' => [
        //         'cookie' => $request->header('cookie'),
        //         'origin' => $request->header('origin'),
        //         'x_guest_token' => $request->header('X-Guest-Token'),
        //     ],
        // ]);

        $cart = Cart::getCart();

        if ($cart) {
            $cart->load([
                'items.product.super_attributes.options',
                'items.product.parent.super_attributes.options',
            ]);
        }

        // Log::info('CART DEBUG (INDEX - AFTER GETCART)', [
        //     'session_id' => session()->getId(),
        //     'cart_found' => $cart ? true : false,
        //     'cart_id' => $cart->id ?? null,
        //     'items_count' => $cart?->items?->count(),
        //     'items' => $cart?->items?->map(function ($item) {
        //         return [
        //             'id' => $item->id,
        //             'product_id' => $item->product_id,
        //             'qty' => $item->quantity,
        //         ];
        //     }),
        // ]);

        if (! $cart) {

            return response()->json([
                'data' => null,
                'message' => 'cart_not_found'
            ]);
        }

        // Log::info('CART INDEX SUCCESS RESPONSE', [
        //     'cart_id' => $cart->id,
        //     'items_count' => $cart->items->count(),
        // ]);

        return response()->json([
            'data' => $this->formatCart($cart),
        ]);
    }

    /*
    |----------------------------------------
    | ADD TO CART
    |----------------------------------------
    */
    public function store(Request $request)
    {
        // Log::info('SPA CART REQUEST', $request->all());

        try {
            $product = $this->productRepository
                ->with('parent')
                ->findOrFail($request->product_id);

            $isVariant = ! is_null($product->parent);

            $rules = [
                'product_id' => 'required|integer|exists:products,id',
                'quantity'   => 'nullable|integer|min:1',
                        ];

            if ($isVariant) {
                $rules['super_attribute'] = 'required|array|min:1';
            } else {
                $rules['super_attribute'] = 'nullable|array';
            }

            $request->validate($rules);

            // Force unique cart item per variant combination
            $options = $request->super_attribute ?? [];
            $cart = Cart::addProduct($product, array_merge($request->all(), [
                'additional' => [
                    'super_attribute' => $options
                ]
            ]));
            Cart::collectTotals();

            $cart = Cart::getCart();

            // Log::info('SPA AFTER ADD PRODUCT', [
            //     'cart_id' => $cart->id,
            //     'items_count' => $cart->items->count()
            // ]);

            return response()->json([
                'message' => 'Added to cart successfully',
                'data' => $this->formatCart($cart),
            ]);
        } catch (\Exception $e) {
            // Log::error('ADD TO CART ERROR', ['message' => $e->getMessage()]);
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /*
    |----------------------------------------
    | UPDATE ITEM
    |----------------------------------------
    */
    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
            'quantity'     => 'required|integer|min:1',
        ]);

        $cart = Cart::getCart();
        if (! $cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        // Correct format expected by Cart::updateItems()
        Cart::updateItems([
            'qty' => [
                $request->cart_item_id => $request->quantity
            ]
        ]);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message' => 'Cart updated successfully',
            'data' => $this->formatCart($cart),
        ]);
    }

    /*
    |----------------------------------------
    | REMOVE ITEM
    |----------------------------------------
    */
    public function destroy(Request $request, $cart_item_id)
    {
        $cart = Cart::getCart();

        if (! $cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        Cart::removeItem($cart_item_id);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message' => 'Cart updated successfully',
            'data' => $this->formatCart($cart),
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | APPLY COUPON
    |--------------------------------------------------------------------------
    */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $cart = Cart::getCart();

        if (! $cart) {
            return response()->json([
                'message' => 'Cart not found.',
            ], 404);
        }

        try {

            $coupon = $this->cartRuleCouponRepository
                ->findOneByField('code', $request->code);

            if (! $coupon) {

                return response()->json([
                    'message' => 'Coupon not found.',
                ], 422);

            }

            Cart::setCouponCode($coupon->code);

            Cart::collectTotals();

            return response()->json([
                'message' => 'Coupon applied.',
                'data' => Cart::getCart(),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE COUPON
    |--------------------------------------------------------------------------
    */
    public function removeCoupon()
    {
        Cart::removeCouponCode();

        Cart::collectTotals();

        return response()->json([
            'message' => 'Coupon removed.',
            'data' => Cart::getCart(),
        ]);
    }

    private function formatCart($cart)
    {
        if ($cart) {
            $cart->load([
                'items.product.super_attributes.options',
                'items.product.parent.super_attributes.options',
            ]);
        }

        return [
            'cart' => $cart,
            'currency_code' => $cart->cart_currency_code,
            'currency_symbol' => core()->currencySymbol($cart->cart_currency_code),
            'items' => $cart->items->map(function ($item) {

                $attributes = [];

                $product = $item->product;

                $parentProduct = $product?->parent ?? $product;

                $superAttributes = $item->additional['super_attribute'] ?? [];

                $hasColor = false;
                $hasSize = false;

                if (! empty($superAttributes)) {

                    foreach ($superAttributes as $attributeId => $optionId) {

                        $attribute = $parentProduct?->super_attributes
                            ?->firstWhere('id', (int) $attributeId);

                        if (! $attribute) {
                            continue;
                        }

                        $option = $attribute->options
                            ?->firstWhere('id', (int) $optionId);

                        if (! $option) {
                            continue;
                        }

                        $code = strtolower($attribute->code);

                        if ($code === 'color') {
                            $hasColor = true;
                        }

                        if ($code === 'size') {
                            $hasSize = true;
                        }

                        $attributes[] = [
                            'name'  => $code,
                            'value' => $option->admin_name ?? $option->label,
                        ];
                    }
                }

                // If color wasn't a super attribute, use the variant's color
                if (! $hasColor && $product?->color) {

                    $color = AttributeOption::find($product->color);

                    if ($color) {
                        $attributes[] = [
                            'name'  => 'color',
                            'value' => $color->admin_name ?? $color->label,
                        ];
                    }
                }

                // If size wasn't a super attribute, use the variant's size
                if (! $hasSize && $product?->size) {

                    $size = AttributeOption::find($product->size);

                    if ($size) {
                        $attributes[] = [
                            'name'  => 'size',
                            'value' => $size->admin_name ?? $size->label,
                        ];
                    }
                }

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $product?->base_image_url,
                    'attributes' => $attributes,
                ];
            }),
        ];
    }
}