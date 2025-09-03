<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\Request as GPTRequest;
use App\Jobs\ProcessGPTRequest;
use App\Services\IAService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestGPTApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:gpt-api
                            {api_key : Chave de API para autenticação}
                            {--prompt=Qual é a capital do Brasil? : Prompt para enviar à IA}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a API do OpenAI ChatGPT enviando uma requisição e aguardando a resposta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKeyValue = $this->argument('api_key');
        $prompt = $this->option('prompt');

        // Verifica se a API key existe
        $apiKey = ApiKey::where('key', $apiKeyValue)->first();
        
        if (!$apiKey) {
            $this->error("API Key não encontrada: {$apiKeyValue}");
            return 1;
        }

        if ($apiKey->status !== 'active') {
            $this->error("API Key não está ativa: {$apiKey->status}");
            return 1;
        }

        $this->info("Testando API OpenAI ChatGPT...");
        $this->info("API Key: {$apiKey->name} ({$apiKey->id})");
        $this->info("Prompt: {$prompt}");
        $this->line('');

        // Cria uma requisição de teste
        $request = new GPTRequest();
        $request->id = (string) Str::uuid();
        $request->api_key_id = $apiKey->id;
        $request->content = json_encode(['prompt' => $prompt]);
        $request->parameters = ['temperature' => 0.7, 'max_tokens' => 100];
        $request->status = 'pending';
        $request->attempts = 0;
        $request->ip_address = '127.0.0.1';
        $request->user_agent = 'Console Command Test';
        $request->save();

        $this->info("Requisição criada: {$request->id}");
        
        // Dispara o job para processar
        ProcessGPTRequest::dispatch($request);
        $this->info("Job despachado para a fila 'gpt-requests'");
        
        // Aguarda o processamento
        $this->info("Aguardando processamento...");
        $maxAttempts = 30;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            sleep(2);
            $request->refresh();
            $attempt++;
            
            $this->line("Tentativa {$attempt}/{$maxAttempts} - Status: {$request->status}");
            
            if ($request->status === 'completed') {
                $this->line('');
                $this->info("✅ Sucesso! Requisição processada em {$request->processing_time}ms");
                
                $result = json_decode($request->result, true);
                
                $this->line('');
                $this->info("📝 Resposta da IA:");
                $this->line($result['response'] ?? 'Resposta não disponível');
                
                $this->line('');
                $this->info("📊 Detalhes:");
                $this->line("Modelo: " . ($result['model'] ?? 'N/A'));
                $this->line("Tokens entrada: " . ($result['tokens_input'] ?? 'N/A'));
                $this->line("Tokens saída: " . ($result['tokens_output'] ?? 'N/A'));
                $this->line("Tempo total: {$request->processing_time}ms");
                
                return 0;
            }
            
            if ($request->status === 'failed') {
                $this->line('');
                $this->error("❌ Falha no processamento!");
                $this->error("Erro: {$request->error}");
                $this->error("Tentativas: {$request->attempts}");
                return 1;
            }
        }
        
        $this->line('');
        $this->error("⏱️ Timeout! A requisição não foi processada em tempo hábil.");
        $this->error("Status atual: {$request->status}");
        
        return 1;
    }
}

