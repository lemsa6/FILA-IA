<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-key
                            {name : Nome para identificar a chave}
                            {--description= : Descrição opcional da chave}
                            {--expires= : Data de expiração no formato YYYY-MM-DD}
                            {--rate-limit-minute=60 : Limite de requisições por minuto}
                            {--rate-limit-hour=1000 : Limite de requisições por hora}
                            {--rate-limit-day=10000 : Limite de requisições por dia}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera uma nova chave de API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $description = $this->option('description');
        $expires = $this->option('expires');
        $rateLimitMinute = $this->option('rate-limit-minute');
        $rateLimitHour = $this->option('rate-limit-hour');
        $rateLimitDay = $this->option('rate-limit-day');

        // Gera uma chave API aleatória
        $key = Str::random(64);

        // Cria o registro no banco de dados
        $apiKey = new ApiKey();
        $apiKey->id = (string) Str::uuid();
        $apiKey->key = $key;
        $apiKey->name = $name;
        $apiKey->description = $description;
        $apiKey->status = 'active';
        $apiKey->rate_limit_minute = $rateLimitMinute;
        $apiKey->rate_limit_hour = $rateLimitHour;
        $apiKey->rate_limit_day = $rateLimitDay;
        
        if ($expires) {
            $apiKey->expires_at = $expires;
        }
        
        $apiKey->save();

        $this->info('Chave API gerada com sucesso!');
        $this->table(
            ['ID', 'Chave', 'Nome', 'Status', 'Expira em'],
            [[$apiKey->id, $apiKey->key, $apiKey->name, $apiKey->status, $apiKey->expires_at ?? 'Nunca']]
        );

        return Command::SUCCESS;
    }
}
