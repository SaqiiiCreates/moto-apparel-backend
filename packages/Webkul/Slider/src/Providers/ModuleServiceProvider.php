<?php

namespace Webkul\Slider\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Slider\Models\Slider;
use Webkul\Slider\Models\Slide;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        Slider::class,
        Slide::class,
    ];
}