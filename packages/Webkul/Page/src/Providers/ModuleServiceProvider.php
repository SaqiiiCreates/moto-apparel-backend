<?php

namespace Webkul\Page\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Page\Models\Page;
use Webkul\Page\Models\PageSection;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        Page::class,
        PageSection::class,         
    ];
}