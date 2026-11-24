<?php

namespace Webkul\Collection\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Collection\Models\Collection;
use Webkul\Collection\Models\CollectionTab;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        Collection::class,
        CollectionTab::class,
    ];
}