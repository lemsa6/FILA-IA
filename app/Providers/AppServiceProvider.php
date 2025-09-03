<?php

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\LogService;
use Illuminate\Support\ServiceProvider;
use App\Services\ClientContextService;
use App\Services\IntelligentAIService;
use App\Services\IAService;

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

        // Registra o serviço de contexto do cliente como singleton
        $this->app->singleton(ClientContextService::class, function ($app) {
            return new ClientContextService();
        });

        // Registra o serviço de IA inteligente como singleton
        $this->app->singleton(IntelligentAIService::class, function ($app) {
            return new IntelligentAIService(
                $app->make(IAService::class),
                $app->make(ClientContextService::class)
            );
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
