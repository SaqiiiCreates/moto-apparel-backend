<?php

namespace Webkul\Admin\Http\Controllers\API;

use App\Http\Controllers\Controller;

class TopBarController extends Controller
{
    public function index()
    {
        return response()->json([
            'left_text'      => core()->getConfigData('general.content.topbar_settings.left_text'),
            'topbar_message' => core()->getConfigData('general.content.topbar_settings.topbar_message'),
            'timezone'       => core()->getConfigData('general.content.topbar_settings.timezone'),
        ]);
    }
}