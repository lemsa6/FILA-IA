<?php

namespace App\Services;

use App\Services\Resilience\CircuitBreaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class OllamaService
{
    /**
     * URL da API do Ollama
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * Modelo a ser usado
     *
     * @var string
     */
    protected $model;

    /**
     * Circuit breaker para o serviço Ollama
     *
     * @var CircuitBreaker
     */
    protected $circuitBreaker;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->apiUrl = config('services.ollama.url');
        $this->model = config('services.ollama.model');
        
        // Inicializa o circuit breaker
        $this->circuitBreaker = new CircuitBreaker(
            'ollama',
            config('services.ollama.circuit_breaker.failure_threshold', 5),
            config('services.ollama.circuit_breaker.reset_timeout', 60)
        );
    }

    /**
     * Envia uma requisição para o Ollama
     *
     * @param string $prompt
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    public function generateCompletion(string $prompt, array $parameters = [])
    {
        return $this->circuitBreaker->execute(
            function () use ($prompt, $parameters) {
                $defaultParams = [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ];

                $params = array_merge($defaultParams, $parameters);

                $response = Http::timeout(30)
                    ->post($this->apiUrl . '/api/generate', $params);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Erro na resposta do Ollama', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('Erro ao comunicar com Ollama: ' . $response->status());
            },
            function () use ($prompt) {
                // Fallback quando o circuit breaker está aberto
                Log::warning('Usando fallback para requisição Ollama (circuit breaker aberto)');
                
                // Verifica se há uma resposta em cache para este prompt
                $cacheKey = 'ollama_fallback:' . md5($prompt);
                $cachedResponse = Cache::get($cacheKey);
                
                if ($cachedResponse) {
                    return $cachedResponse;
                }
                
                // Se não há cache, retorna uma resposta padrão
                return [
                    'model' => $this->model,
                    'response' => 'Desculpe, o serviço de IA está temporariamente indisponível. Por favor, tente novamente mais tarde.',
                    'done' => true,
                    '_fallback' => true,
                ];
            }
        );
    }

    /**
     * Verifica a saúde do serviço Ollama
     *
     * @return bool
     */
    public function healthCheck()
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl);
            return $response->successful();
        } catch (Exception $e) {
            Log::error('Falha no health check do Ollama', [
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }
} 