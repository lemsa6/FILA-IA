<?php

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\LogService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registra o serviço de criptografia como singleton
        $this->app->singleton(EncryptionService::class, function ($app) {
            return new EncryptionService();
        });

        // Registra o serviço de logs persistentes como singleton
        $this->app->singleton(LogService::class, function ($app) {
            return new LogService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
