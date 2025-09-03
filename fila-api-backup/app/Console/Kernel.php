<?php

namespace App\Console;

use App\Console\Commands\CleanupLogs;
use App\Console\Commands\GenerateApiKey;
use App\Console\Commands\TestOllamaApi;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        GenerateApiKey::class,
        CleanupLogs::class,
        TestOllamaApi::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Limpa logs antigos diariamente
        $schedule->command('logs:cleanup')
            ->daily()
            ->at('01:00')
            ->appendOutputTo(storage_path('logs/scheduler.log'));
            
        // Verifica a saúde do serviço Ollama a cada 5 minutos
        $schedule->call(function () {
            app(\App\Services\OllamaService::class)->healthCheck();
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 