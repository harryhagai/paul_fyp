<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $moneyFormatHelpersPath = app_path('Support/money_format.php');
        if (is_file($moneyFormatHelpersPath)) {
            require_once $moneyFormatHelpersPath;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if (!Schema::hasTable('site_settings')) {
            return;
        }

        $mailSettings = SiteSetting::query()
            ->where('group', 'mail')
            ->pluck('value', 'key');

        if ($mailSettings->isEmpty()) {
            return;
        }

        $mailEncryption = $mailSettings->get('mail_encryption', config('mail.mailers.smtp.encryption'));
        $mailScheme = match ($mailEncryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => config('mail.mailers.smtp.scheme'),
        };

        config([
            'mail.default' => $mailSettings->get('mail_mailer', config('mail.default')),
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $mailScheme,
            'mail.mailers.smtp.host' => $mailSettings->get('mail_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => (int) $mailSettings->get('mail_port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => $mailEncryption,
            'mail.mailers.smtp.username' => $mailSettings->get('mail_username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => $mailSettings->get('mail_password', config('mail.mailers.smtp.password')),
            'mail.from.address' => $mailSettings->get('mail_from_address', config('mail.from.address')),
            'mail.from.name' => $mailSettings->get('mail_from_name', config('mail.from.name')),
        ]);
    }
}
