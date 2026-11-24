<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    public function __construct(
        protected \Webkul\Shop\Http\Controllers\API\OnepageController $onepage
    ) {}

    public function storeAddress(\Webkul\Shop\Http\Requests\CartAddressRequest $request)
    {
        return $this->onepage->storeAddress($request);
    }

    public function storeShippingMethod()
    {
        return $this->onepage->storeShippingMethod();
    }

    public function storePaymentMethod()
    {
        return $this->onepage->storePaymentMethod();
    }

    public function storeOrder()
    {
        return $this->onepage->storeOrder();
    }
}