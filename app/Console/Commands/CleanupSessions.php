<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Services\ClientContextService;
use Illuminate\Console\Command;

class CleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup {--hours=24 : Número de horas para considerar sessão como antiga}';

    /**
     * The console console description.
     *
     * @var string
     */
    protected $description = 'Limpa sessões antigas de todos os clientes';

    /**
     * Execute the console command.
     */
    public function handle(ClientContextService $clientContextService)
    {
        $hours = $this->option('hours');
        $this->info("Limpando sessões mais antigas que {$hours} horas...");

        $apiKeys = ApiKey::where('status', 'active')->get();
        $totalCleaned = 0;

        foreach ($apiKeys as $apiKey) {
            $cleaned = $clientContextService->cleanupOldSessions($apiKey->id);
            $totalCleaned += $cleaned;
            
            if ($cleaned > 0) {
                $this->info("Cliente {$apiKey->name}: {$cleaned} sessões limpas");
            }
        }

        $this->info("Total de sessões limpas: {$totalCleaned}");
        
        return Command::SUCCESS;
    }
}
