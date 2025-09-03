<?php

namespace App\Jobs;

use App\Models\Request as GPTRequest;
use App\Models\PlanAssignment;
use App\Services\IAService;
use App\Services\TokenTrackingService;
use Exception;
use Illuminate\Bus\Queueable;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessGPTRequest implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de tentativas
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Tempo de espera entre tentativas em segundos
     *
     * @var array
     */
    public $backoff = [10, 60, 300]; // 10s, 1min, 5min

    /**
     * A requisição a ser processada
     *
     * @var \App\Models\Request
     */
    protected $request;

    /**
     * Tempo mínimo entre o início de requisições em segundos
     * 
     * @var int
     */
    protected $minTimeBetweenRequests = 0; // Removido delay desnecessário

    /**
     * Chave de cache para controlar o tempo da última requisição
     * 
     * @var string
     */
    protected $lastRequestTimeKey = 'gpt_last_request_time';

    /**
     * Create a new job instance.
     */
    public function __construct(GPTRequest $request)
    {
        $this->request = $request;
        $this->queue = 'gpt-requests';
    }

    /**
     * O identificador único do job.
     * Garante ordem sequencial por cliente, mas permite paralelismo entre clientes diferentes
     *
     * @return string
     */
    public function uniqueId()
    {
        // Cada cliente tem sua própria fila sequencial
        // Clientes diferentes podem processar em paralelo
        return "gpt:client_{$this->request->api_key_id}";
    }

    /**
     * Tempo limite para manter a unicidade (em segundos)
     * 
     * @return int
     */
    public function uniqueFor()
    {
        // Mantém unicidade por 5 minutos máximo
        // Se um job travar, outro pode assumir após esse tempo
        return 300;
    }

    /**
     * Execute the job.
     */
    public function handle(IAService $iaService, TokenTrackingService $tokenTracking): void
    {
        try {
            // Verifica quando foi a última requisição para este cliente específico
            $clientLastRequestKey = "gpt:client_{$this->request->api_key_id}:last_request_time";
            $lastRequestTime = Cache::get($clientLastRequestKey);
            $now = microtime(true);
            
            if ($lastRequestTime) {
                $timeSinceLastRequest = $now - $lastRequestTime;
                
                // Se não passou tempo suficiente desde o início da última requisição, aguarda
                if ($timeSinceLastRequest < $this->minTimeBetweenRequests) {
                    $sleepTime = ceil($this->minTimeBetweenRequests - $timeSinceLastRequest);
                    Log::info("Aguardando {$sleepTime}s para respeitar o intervalo mínimo entre requisições do cliente", [
                        'request_id' => $this->request->id,
                        'api_key_id' => $this->request->api_key_id,
                    ]);
                    sleep($sleepTime);
                }
            }
            
            // Marca o tempo de início desta requisição para este cliente
            Cache::put($clientLastRequestKey, microtime(true), 3600);

            // Atualiza o status da requisição
            $this->request->status = 'processing';
            $this->request->attempts += 1;
            $this->request->save();

            // Decodifica o conteúdo da requisição
            $content = json_decode($this->request->content, true);
            $prompt = $content['prompt'] ?? '';
            $parameters = $this->request->parameters ?? $content['parameters'] ?? [];
            $metadata = $this->request->metadata ?? $content['metadata'] ?? [];
            $sessionId = $this->request->session_id ?? $content['session_id'] ?? null;

            // Registra o tempo de início
            $startTime = microtime(true);

            // Processa a requisição com isolamento por cliente usando OpenAI ChatGPT
            $result = $iaService->generateCompletion(
                $prompt, 
                $parameters, 
                $this->request->api_key_id,
                $sessionId
            );

            // Adiciona metadados ao resultado
            $result['metadata'] = $metadata;

            // Calcula o tempo de processamento
            $processingTime = (int) ((microtime(true) - $startTime) * 1000); // em milissegundos

            // Atualiza a requisição com o resultado
            $this->request->status = 'completed';
            $this->request->result = json_encode($result);
            $this->request->model = $result['model'] ?? 'gpt-4.1-nano';
            $this->request->processing_time = $processingTime;
            $this->request->response_time = $processingTime;
            $this->request->tokens_input = $result['tokens_input'] ?? null;
            $this->request->tokens_output = $result['tokens_output'] ?? null;
            $this->request->completed_at = now();
            $this->request->save();

            // Rastreia o uso de tokens se disponível
            if ($result['tokens_input'] && $result['tokens_output']) {
                try {
                    // Obtém o plano ativo da API Key
                    $activeAssignment = PlanAssignment::where('api_key_id', $this->request->api_key_id)
                        ->where('status', 'active')
                        ->first();

                    if ($activeAssignment) {
                        // Calcula custos baseados no modelo GPT-4.1-nano
                        $costUsd = $this->calculateCost(
                            $result['tokens_input'],
                            $result['tokens_output'],
                            $result['model'] ?? 'gpt-4.1-nano'
                        );
                        $costBrl = $costUsd * 5.5; // Taxa de câmbio aproximada

                        // Registra o uso de tokens
                        $tokenTracking->trackTokenUsage(
                            $this->request->api_key_id,
                            $activeAssignment->plan_id,
                            $this->request->id,
                            $sessionId,
                            $prompt,
                            $result['response'] ?? '',
                            $result['tokens_input'],
                            $result['tokens_output'],
                            $result['model'] ?? 'gpt-4.1-nano',
                            $costUsd,
                            $costBrl
                        );

                        Log::info('Uso de tokens rastreado com sucesso', [
                            'request_id' => $this->request->id,
                            'api_key_id' => $this->request->api_key_id,
                            'plan_id' => $activeAssignment->plan_id,
                            'tokens_input' => $result['tokens_input'],
                            'tokens_output' => $result['tokens_output'],
                            'cost_usd' => $costUsd,
                            'cost_brl' => $costBrl,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning('Erro ao rastrear uso de tokens', [
                        'request_id' => $this->request->id,
                        'api_key_id' => $this->request->api_key_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Requisição GPT processada com sucesso', [
                'request_id' => $this->request->id,
                'api_key_id' => $this->request->api_key_id,
                'session_id' => $sessionId,
                'processing_time' => $processingTime,
                'model' => $result['model'] ?? 'gpt-4.1-nano',
                'cache_hit' => $result['_fallback'] ?? false,
            ]);
        } catch (Exception $e) {
            // Registra o erro
            Log::error('Erro ao processar requisição GPT', [
                'request_id' => $this->request->id,
                'api_key_id' => $this->request->api_key_id,
                'error' => $e->getMessage(),
                'attempt' => $this->request->attempts,
            ]);

            // Se atingiu o número máximo de tentativas, marca como falha
            if ($this->attempts() >= $this->tries) {
                $this->request->status = 'failed';
                $this->request->error = $e->getMessage();
                $this->request->error_message = $e->getMessage();
                $this->request->save();
                
                Log::error('Requisição GPT falhou após todas as tentativas', [
                    'request_id' => $this->request->id,
                    'api_key_id' => $this->request->api_key_id,
                    'final_error' => $e->getMessage(),
                ]);
            } else {
                // Caso contrário, lança a exceção para que o job seja tentado novamente
                throw $e;
            }
        }
    }

    /**
     * Calcula o custo baseado no número de tokens e modelo GPT
     */
    private function calculateCost(int $tokensInput, int $tokensOutput, string $model): float
    {
        // Preços do GPT-4.1-nano (por 1M tokens)
        $inputPricePerMillion = 0.20;  // $0.20 por 1M tokens de entrada
        $outputPricePerMillion = 0.80; // $0.80 por 1M tokens de saída

        // Calcula custos
        $inputCost = ($tokensInput / 1_000_000) * $inputPricePerMillion;
        $outputCost = ($tokensOutput / 1_000_000) * $outputPricePerMillion;

        return $inputCost + $outputCost;
    }
}
