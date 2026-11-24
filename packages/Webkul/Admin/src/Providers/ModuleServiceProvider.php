<?php

namespace Webkul\Admin\Providers;

use Illuminate\Support\Facades\Route;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [];

    public function boot(): void
    {
        parent::boot();

        Route::middleware(['web', 'admin'])
            ->group(__DIR__ . '/../Routes/navigation-route.php');
    }
}