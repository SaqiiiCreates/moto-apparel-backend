<?php

namespace Webkul\Banner\Providers;

use Illuminate\Support\ServiceProvider;

class BannerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');        
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api-routes.php');        
        
        $this->mergeConfigFrom(__DIR__ . '/../Config/menu.php', 'menu.admin');        
        $this->mergeConfigFrom(__DIR__ . '/../Config/acl.php', 'acl');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'banner');
    }
}
