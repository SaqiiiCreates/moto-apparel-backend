<?php

namespace Webkul\Admin\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Webkul\Core\Facades\Core;

class FooterLinksController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'tagline' => Core::getConfigData('general.content.footer_links.tagline'),

            'links' => [
                [
                    'label' => Core::getConfigData('general.content.footer_links.link_1_label'),
                    'url'   => Core::getConfigData('general.content.footer_links.link_1_url'),
                ],
                [
                    'label' => Core::getConfigData('general.content.footer_links.link_2_label'),
                    'url'   => Core::getConfigData('general.content.footer_links.link_2_url'),
                ],
                [
                    'label' => Core::getConfigData('general.content.footer_links.link_3_label'),
                    'url'   => Core::getConfigData('general.content.footer_links.link_3_url'),
                ],
            ],
        ]);
    }
}