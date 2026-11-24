<?php

namespace App\Providers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        // LOAD API ROUTES MANUALLY
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        // === FOR WAITLIST NOTIFICATION ===
        \Webkul\Product\Models\ProductInventory::observe(\Webkul\Product\Observers\ProductWaitlistObserver::class);

        try {
            $smtpHost = core()->getConfigData('emails.smtp.settings.host');

            if ($smtpHost) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $smtpHost,
                    'mail.mailers.smtp.port' => core()->getConfigData('emails.smtp.settings.port') ?: 587,
                    'mail.mailers.smtp.username' => core()->getConfigData('emails.smtp.settings.username'),
                    'mail.mailers.smtp.password' => core()->getConfigData('emails.smtp.settings.password'),
                    'mail.mailers.smtp.encryption' => core()->getConfigData('emails.smtp.settings.encryption') ?: null,
                ]);
            }

            $fromAddress = core()->getConfigData('emails.configure.email_settings.shop_email_from');
            $fromName = core()->getConfigData('emails.configure.email_settings.sender_name');

            if ($fromAddress) {
                config([
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName ?: config('mail.from.name'),
                ]);
            }
        } catch (\Throwable $e) {
            // installer / artisan without DB
        }
    }
}