<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;

class CheckoutFooterLinkController extends Controller
{
    public function index()
    {
        $enabled = core()->getConfigData('sales.checkout.footer_links.enabled');

        if ($enabled === '0' || $enabled === 0 || $enabled === false) {
            return response()->json(['data' => []]);
        }

        $links = [];

        for ($i = 1; $i <= 6; $i++) {
            $label = trim((string) core()->getConfigData("sales.checkout.footer_links.link_{$i}_label"));
            $url   = trim((string) core()->getConfigData("sales.checkout.footer_links.link_{$i}_url"));

            if ($label === '') {
                continue;
            }

            $links[] = [
                'label' => $label,
                'url'   => $url,
            ];
        }

        return response()->json([
            'data' => $links,
        ]);
    }
}