<?php

namespace App\Payment;

use Webkul\Payment\Payment\Payment;

class Stripe extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'stripe';

    /**
     * No redirect is needed because payment is handled
     * on the frontend via Stripe Elements / Express Checkout.
     */
    public function getRedirectUrl()
    {
        return null;
    }
}