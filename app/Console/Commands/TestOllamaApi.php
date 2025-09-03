<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\Request as OllamaRequest;
use App\Jobs\ProcessOllamaRequest;
use App\Services\OllamaService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestOllamaApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ollama-api
                            {api_key : Chave de API para autenticação}
                            {--prompt=Qual é a capital do Brasil? : Prompt para enviar à IA}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a API do Ollama enviando uma requisição e aguardando a resposta';

    /**
     * Execute the console command.
     */
    public function handle(OllamaService $ollamaService)
    {
        $apiKeyString = $this->argument('api_key');
        $prompt = $this->option('prompt');

        $this->info('Testando a API do Ollama');
        $this->info('Chave API: ' . substr($apiKeyString, 0, 10) . '...');
        $this->info('Prompt: ' . $prompt);

        // Verificar se a chave API existe
        $apiKey = ApiKey::where('key', $apiKeyString)->first();
        if (!$apiKey) {
            $this->error('❌ Chave API inválida');
            return Command::FAILURE;
        }

        $this->info('✅ Chave API válida: ' . $apiKey->name);

        // Criar a requisição diretamente
        $this->info("\nCriando requisição...");
        $ollamaRequest = new OllamaRequest();
        $ollamaRequest->id = (string) Str::uuid();
        $ollamaRequest->api_key_id = $apiKey->id;
        $ollamaRequest->content = json_encode([
            'prompt' => $prompt,
            'parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 100
            ]
        ]);
        $ollamaRequest->status = 'pending';
        $ollamaRequest->priority = 0;
        $ollamaRequest->save();

        $this->info('✅ Requisição criada com ID: ' . $ollamaRequest->id);

        // Processar a requisição diretamente (sem usar a fila)
        $this->info("\nProcessando requisição...");
        
        try {
            // Atualiza o status da requisição
            $ollamaRequest->status = 'processing';
            $ollamaRequest->attempts += 1;
            $ollamaRequest->save();

            // Registra o tempo de início
            $startTime = microtime(true);

            // Processa a requisição
            $result = $ollamaService->generateCompletion($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 100
            ]);

            // Calcula o tempo de processamento
            $processingTime = (int) ((microtime(true) - $startTime) * 1000); // em milissegundos

            // Atualiza a requisição com o resultado
            $ollamaRequest->status = 'completed';
            $ollamaRequest->result = json_encode($result);
            $ollamaRequest->processing_time = $processingTime;
            $ollamaRequest->completed_at = now();
            $ollamaRequest->save();

            $this->info('✅ Requisição processada com sucesso!');
            $this->info('Tempo de processamento: ' . $processingTime . ' ms');
            
            $this->info("\nResposta da IA:");
            $this->line('<fg=blue>' . json_encode($result, JSON_PRETTY_PRINT) . '</>');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao processar requisição: ' . $e->getMessage());
            
            // Atualiza a requisição com o erro
            $ollamaRequest->status = 'failed';
            $ollamaRequest->error = $e->getMessage();
            $ollamaRequest->save();
            
            return Command::FAILURE;
        }
    }
} 