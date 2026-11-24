<?php

namespace Webkul\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Core\Repositories\CoreConfigRepository;

class MailConfigServiceProvider extends ServiceProvider
{
    public function boot(CoreConfigRepository $coreConfig)
    {
        $configs = $coreConfig->all();

        $map = $configs->pluck('value', 'code');

        config([
            'mail.mailers.smtp.host' => !empty($map['emails.smtp.settings.host'])
                ? $map['emails.smtp.settings.host']
                : config('mail.mailers.smtp.host'),

            'mail.mailers.smtp.port' => !empty($map['emails.smtp.settings.port'])
                ? $map['emails.smtp.settings.port']
                : config('mail.mailers.smtp.port'),

            'mail.mailers.smtp.username' => !empty($map['emails.smtp.settings.username'])
                ? $map['emails.smtp.settings.username']
                : config('mail.mailers.smtp.username'),

            'mail.mailers.smtp.password' => array_key_exists('emails.smtp.settings.password', $map->all())
                ? $map['emails.smtp.settings.password']
                : config('mail.mailers.smtp.password'),

            'mail.mailers.smtp.encryption' => array_key_exists('emails.smtp.settings.encryption', $map->all())
                ? $map['emails.smtp.settings.encryption']
                : config('mail.mailers.smtp.encryption'),
        ]);
    }
}