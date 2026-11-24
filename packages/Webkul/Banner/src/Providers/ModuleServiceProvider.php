<?php

namespace Webkul\Banner\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Banner\Models\Banner;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        Banner::class,        
    ];
}