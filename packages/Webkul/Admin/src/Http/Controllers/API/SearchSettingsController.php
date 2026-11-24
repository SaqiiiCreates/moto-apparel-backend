<?php

namespace Webkul\Admin\Http\Controllers\API;

use App\Http\Controllers\Controller;

class SearchSettingsController extends Controller
{
    public function index()
    {
        return response()->json([
            'explore' => [
                [
                    'label' => core()->getConfigData('general.content.search_settings.explore_item_1_label'),
                    'url'   => core()->getConfigData('general.content.search_settings.explore_item_1_url'),
                ],
                [
                    'label' => core()->getConfigData('general.content.search_settings.explore_item_2_label'),
                    'url'   => core()->getConfigData('general.content.search_settings.explore_item_2_url'),
                ],
                [
                    'label' => core()->getConfigData('general.content.search_settings.explore_item_3_label'),
                    'url'   => core()->getConfigData('general.content.search_settings.explore_item_3_url'),
                ],
                [
                    'label' => core()->getConfigData('general.content.search_settings.explore_item_4_label'),
                    'url'   => core()->getConfigData('general.content.search_settings.explore_item_4_url'),
                ],
                [
                    'label' => core()->getConfigData('general.content.search_settings.explore_item_5_label'),
                    'url'   => core()->getConfigData('general.content.search_settings.explore_item_5_url'),
                ],
            ],
        ]);
    }
}