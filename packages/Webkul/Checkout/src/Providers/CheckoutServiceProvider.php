<?php

namespace Webkul\Checkout\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Checkout\Listeners\CustomerEventsHandler;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        include __DIR__.'/../Http/helpers.php';
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->register(EventServiceProvider::class);
        $this->app->register(ModuleServiceProvider::class);

        $this->app['events']->subscribe(CustomerEventsHandler::class);
    }
}
