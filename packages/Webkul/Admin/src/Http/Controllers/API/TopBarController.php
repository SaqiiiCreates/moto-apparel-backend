<?php

namespace Webkul\Admin\Http\Controllers\API;

use App\Http\Controllers\Controller;

class TopBarController extends Controller
{
    public function index()
    {
        return response()->json([
            'left_text'        => core()->getConfigData('general.content.topbar_settings.left_text'),
            'marquee_messages' => core()->getConfigData('general.content.topbar_settings.marquee_messages'),
            'timezone'         => core()->getConfigData('general.content.topbar_settings.timezone'),
        ]);
    }
}