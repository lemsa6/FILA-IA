<?php

namespace App\Jobs;

use App\Models\Request as GPTRequest;
use App\Services\IAService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FastProcessGPTRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2; // Reduzido de 3 para 2
    public $timeout = 20; // Reduzido de 25 para 20s
    public $maxExceptions = 2;

    protected GPTRequest $request;

    public function __construct(GPTRequest $request)
    {
        $this->request = $request;
        $this->queue = 'gpt-requests';
    }

    /**
     * Execute the job - ULTRA OTIMIZADO
     */
    public function handle(IAService $iaService): void
    {
        $startTime = microtime(true);
        
        try {
            // ⚡ Processa com IA Service otimizado (sem DB update inicial)
            $result = $iaService->generateCompletion(
                $this->getPromptFromRequest(),
                $this->getParametersFromRequest(),
                $this->request->api_key_id,
                $this->request->session_id ?? null
            );

            if (!$result || !isset($result['response'])) {
                throw new Exception('Resposta inválida da IA');
            }

            // ⚡ Tracking simples de tokens (assíncrono)
            $this->trackTokensAsync($result);

            // ⚡ UMA ÚNICA operação de banco com todos os dados
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            // 💰 Calcula custos baseados no modelo
            $tokensInput = $result['tokens_input'] ?? 0;
            $tokensOutput = $result['tokens_output'] ?? 0;
            $model = $result['model'] ?? 'gpt-4.1-nano';
            
            $costUsd = $this->calculateCost($tokensInput, $tokensOutput, $model);
            $costBrl = $costUsd * 5.5; // Taxa de câmbio aproximada
            
            $this->request->update([
                'status' => 'completed',
                'started_at' => now()->subMilliseconds($processingTime), // Calcula o tempo de início
                'result' => json_encode($result),
                'processing_time' => $processingTime,
                'completed_at' => now(),
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
                'model' => $model,
                'cost_usd' => $costUsd,
                'cost_brl' => $costBrl,
            ]);

            // Log otimizado apenas para requests lentos (>3s) ou com cache miss
            if ($processingTime > 3000 || !($result['_cache_hit'] ?? false)) {
                Log::info('GPT request completed', [
                    'request_id' => $this->request->id,
                    'api_key_id' => $this->request->api_key_id,
                    'processing_time' => $processingTime,
                    'tokens_in' => $result['tokens_input'] ?? 0,
                    'tokens_out' => $result['tokens_output'] ?? 0,
                    'cache_hit' => $result['_cache_hit'] ?? false
                ]);
            }

        } catch (Exception $e) {
            $this->handleFailure($e, $startTime);
        }
    }

    /**
     * Tracking assíncrono de tokens (não bloqueia a resposta)
     */
    private function trackTokensAsync(array $result): void
    {
        // Apenas incrementa contadores Redis - zero DB queries
        $apiKeyId = $this->request->api_key_id;
        $tokensIn = $result['tokens_input'] ?? 0;
        $tokensOut = $result['tokens_output'] ?? 0;
        $totalTokens = $tokensIn + $tokensOut;
        
        // Contadores por período
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        
        // Incrementa contadores (expira automaticamente)
        Cache::increment("tokens:daily:{$apiKeyId}:{$today}", $totalTokens);
        Cache::increment("tokens:monthly:{$apiKeyId}:{$thisMonth}", $totalTokens);
        Cache::increment("tokens:total:{$apiKeyId}", $totalTokens);
        
        // Define TTL se for o primeiro incremento
        if (Cache::get("tokens:daily:{$apiKeyId}:{$today}") === $totalTokens) {
            Cache::expire("tokens:daily:{$apiKeyId}:{$today}", 86400); // 1 dia
        }
        
        if (Cache::get("tokens:monthly:{$apiKeyId}:{$thisMonth}") === $totalTokens) {
            Cache::expire("tokens:monthly:{$apiKeyId}:{$thisMonth}", 2592000); // 30 dias
        }
    }

    /**
     * Handle job failure
     */
    private function handleFailure(Exception $e, float $startTime): void
    {
        $processingTime = round((microtime(true) - $startTime) * 1000);
        $this->request->attempts++;

        if ($this->request->attempts >= $this->tries) {
            $this->request->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'processing_time' => $processingTime,
                'completed_at' => now(),
            ]);

            Log::error('GPT request failed permanently', [
                'request_id' => $this->request->id,
                'api_key_id' => $this->request->api_key_id,
                'error' => $e->getMessage(),
                'attempts' => $this->request->attempts,
                'processing_time' => $processingTime
            ]);
        } else {
            $this->request->save();
            
            Log::warning('GPT request failed, retrying', [
                'request_id' => $this->request->id,
                'api_key_id' => $this->request->api_key_id,
                'error' => $e->getMessage(),
                'attempt' => $this->request->attempts,
                'processing_time' => $processingTime
            ]);
            
            throw $e; // Re-throw para retry
        }
    }

    /**
     * Extract prompt from request content
     */
    private function getPromptFromRequest(): string
    {
        $content = json_decode($this->request->content, true);
        return $content['prompt'] ?? $content['message'] ?? '';
    }

    /**
     * Extract parameters from request
     */
    private function getParametersFromRequest(): array
    {
        return $this->request->parameters ?? [];
    }

    /**
     * Calcula o custo baseado no número de tokens e modelo GPT
     */
    private function calculateCost(int $tokensInput, int $tokensOutput, string $model): float
    {
        // Preços do GPT-4.1-nano (por 1M tokens) - Atualizados em Out/2025
        $inputPricePerMillion = 0.20;  // $0.20 por 1M tokens de entrada
        $outputPricePerMillion = 0.80; // $0.80 por 1M tokens de saída

        // Calcula custos
        $inputCost = ($tokensInput / 1_000_000) * $inputPricePerMillion;
        $outputCost = ($tokensOutput / 1_000_000) * $outputPricePerMillion;

        return round($inputCost + $outputCost, 6); // 6 casas decimais para precisão
    }
}

