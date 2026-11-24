<?php

namespace Webkul\Admin\Http\Controllers\API;

use App\Http\Controllers\Controller;

class SeoController extends Controller
{
    public function index()
    {
        return response()->json([
            'title' => core()->getConfigData('general.seo.metadata.title')
                ?? 'CORVIT — Built for the long road',

            'description' => core()->getConfigData('general.seo.metadata.description')
                ?? 'CORVIT designs essentials with weight, restraint, and purpose. No trends. No seasons. Just things that last.',
        ]);
    }
}