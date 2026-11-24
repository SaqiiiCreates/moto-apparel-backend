<?php

namespace Webkul\Admin\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;

class DesignSettingsController
{
    public function index()
    {
        $logo = core()->getConfigData('general.design.admin_logo.logo_image');
        $favicon = core()->getConfigData('general.design.admin_logo.favicon');

        $toUrl = function ($path) {
            if (! $path) {
                return null;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return url(Storage::url($path));
        };

        return response()->json([
            'logo' => $toUrl($logo),
            'favicon' => $toUrl($favicon),
        ]);
    }
}