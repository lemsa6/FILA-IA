<?php

namespace App\Services;

use App\Services\Resilience\CircuitBreaker;
use App\Services\TokenUsageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class IAService
{
    /**
     * URL da API de IA (OpenAI GPT-5)
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * Modelo a ser usado (GPT-5 ou equivalente)
     *
     * @var string
     */
    public $model;

    /**
     * Chave da API de IA
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Circuit breaker para o serviço de IA
     *
     * @var CircuitBreaker
     */
    protected $circuitBreaker;

    /**
     * Construtor
     */
    public function __construct()
    {
        // Usando configurações OpenAI em vez de Ollama
        $this->apiUrl = config('services.openai.url', 'https://api.openai.com/v1');
        
        // Garante que o modelo seja uma string válida
        $configModel = config('services.openai.model', 'gpt-4.1-nano');
        $this->model = is_array($configModel) ? 'gpt-4.1-nano' : $configModel;
        
        $this->apiKey = config('services.openai.api_key');
        
        // Inicializa o circuit breaker para OpenAI ChatGPT
        $this->circuitBreaker = new CircuitBreaker(
            'gpt', // Atualizado para GPT
            config('services.openai.circuit_breaker.failure_threshold', 3),
            config('services.openai.circuit_breaker.reset_timeout', 30)
        );
    }

    /**
     * Envia uma requisição para a IA (OpenAI GPT-5) com isolamento por cliente
     *
     * @param string $prompt
     * @param array $parameters
     * @param string $apiKeyId ID da chave API para isolamento
     * @param string|null $sessionId ID da sessão para contexto
     * @return array
     * @throws Exception
     */
    public function generateCompletion(string $prompt, array $parameters = [], string $apiKeyId = null, ?string $sessionId = null)
    {
        // Gera chaves únicas para este cliente
        $promptHash = md5($prompt . json_encode($parameters));
        $clientCacheKey = "gpt:client_{$apiKeyId}:prompt_{$promptHash}";
        $clientContextKey = "gpt:client_{$apiKeyId}:context_{$sessionId}";
        
        // Verifica cache do cliente primeiro (otimizado)
        $cachedResponse = Cache::get($clientCacheKey);
        if ($cachedResponse) {
            // Adiciona flag de cache hit para otimização de logs
            $cachedResponse['_cache_hit'] = true;
            return $cachedResponse;
        }

        return $this->circuitBreaker->execute(
            function () use ($prompt, $parameters, $apiKeyId, $sessionId, $clientCacheKey, $clientContextKey) {
                // Obtém contexto do cliente se existir
                $clientContext = Cache::get($clientContextKey, []);
                $conversationHistory = $clientContext['history'] ?? [];
                
                // Converte histórico para formato OpenAI messages
                $messages = $this->buildOpenAIMessages($prompt, $conversationHistory);
                
                // Prepara parâmetros OpenAI com compatibilidade para GPT-5
                $defaultParams = $this->buildOpenAIParams($parameters, $messages);

                // Remove parâmetros que já foram processados e garante que seja array
                $parametersArray = is_array($parameters) ? $parameters : $parameters->toArray();
                $filteredParams = array_diff_key($parametersArray, ['temperature' => '', 'max_tokens' => '']);
                $params = array_merge($defaultParams, $filteredParams);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->timeout(15)->post($this->apiUrl . '/chat/completions', $params);

                if ($response->successful()) {
                    $openaiResult = $response->json();
                    
                    // Converte resposta OpenAI para formato compatível
                    $result = $this->convertOpenAIResponse($openaiResult);
                    
                    // Atualiza contexto do cliente
                    $this->updateClientContext($clientContextKey, $prompt, $result, $conversationHistory);
                    
                    // Salva no cache do cliente
                    Cache::put($clientCacheKey, $result, config('services.openai.cache.ttl', 3600));
                    
                    // Se estava em fallback, reseta o circuit breaker
                    if (Cache::get("circuit_breaker:gpt:state") === 'open') {
                        $this->circuitBreaker->reset();
                        Log::info('Serviço OpenAI ChatGPT recuperado, circuit breaker resetado automaticamente');
                    }
                    
                    return $result;
                }

                Log::error('Erro na resposta da IA', [
                    'api_key_id' => $apiKeyId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('Erro ao comunicar com IA: ' . $response->status());
            },
            function () use ($prompt, $apiKeyId, $sessionId, $clientCacheKey) {
                // Fallback quando o circuit breaker está aberto
                Log::warning('Usando fallback para requisição OpenAI ChatGPT (circuit breaker aberto)', [
                    'api_key_id' => $apiKeyId,
                    'session_id' => $sessionId
                ]);
                
                // Verifica se há uma resposta em cache para este cliente
                $cachedResponse = Cache::get($clientCacheKey);
                
                if ($cachedResponse) {
                    return $cachedResponse;
                }
                
                // Tenta fazer uma verificação rápida se o serviço voltou
                try {
                    $quickCheck = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json'
                    ])->timeout(3)->get($this->apiUrl . '/models');
                    
                    if ($quickCheck->successful()) {
                        Log::info('Serviço de IA voltou durante fallback, tentando requisição normal');
                        
                        // Se o serviço voltou, tenta a requisição normal
                        $messages = [['role' => 'user', 'content' => $prompt]];
                        
                        // Usa parâmetros corretos para o modelo atual
                        $fallbackParams = $this->buildOpenAIParams(['max_tokens' => 500], $messages);
                        
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Content-Type' => 'application/json'
                        ])->timeout(15)->post($this->apiUrl . '/chat/completions', $fallbackParams);
                        
                        if ($response->successful()) {
                            $openaiResult = $response->json();
                            $result = $this->convertOpenAIResponse($openaiResult);
                            
                            // Reseta o circuit breaker automaticamente
                            $this->circuitBreaker->reset();
                            
                            Log::info('Serviço de IA recuperado automaticamente, circuit breaker resetado');
                            
                            return $result;
                        }
                    }
                } catch (Exception $e) {
                    Log::debug('Verificação rápida falhou durante fallback', [
                        'exception' => $e->getMessage()
                    ]);
                }
                
                // Se não há cache e o serviço não voltou, retorna uma resposta padrão
                return [
                    'model' => $this->model,
                    'response' => 'Desculpe, o serviço OpenAI ChatGPT está temporariamente indisponível. Por favor, tente novamente mais tarde.',
                    'done' => true,
                    '_fallback' => true,
                    '_client_id' => $apiKeyId,
                ];
            }
        );
    }

    /**
     * Constrói mensagens no formato OpenAI
     *
     * @param string $prompt
     * @param array $conversationHistory
     * @return array
     */
    public function buildOpenAIMessages(string $prompt, array $conversationHistory): array
    {
        $messages = [];
        
        // Adiciona mensagem de sistema (opcional)
        $messages[] = [
            'role' => 'system',
            'content' => 'Você é um assistente inteligente e útil. Responda de forma clara e precisa.'
        ];
        
        // Adiciona histórico da conversa (limitado e otimizado)
        if (!empty($conversationHistory)) {
            $recentHistory = array_slice($conversationHistory, -5); // Reduzido para 5 interações
            
            foreach ($recentHistory as $interaction) {
                // Limita tamanho das mensagens para evitar overhead
                $userContent = strlen($interaction['prompt']) > 500 ? 
                    substr($interaction['prompt'], 0, 500) . '...' : 
                    $interaction['prompt'];
                    
                $assistantContent = strlen($interaction['response']) > 1000 ? 
                    substr($interaction['response'], 0, 1000) . '...' : 
                    $interaction['response'];
                
                $messages[] = [
                    'role' => 'user',
                    'content' => $userContent
                ];
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $assistantContent
                ];
            }
        }
        
        // Adiciona a pergunta atual
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        
        return $messages;
    }

    /**
     * Converte resposta OpenAI para formato compatível com Ollama
     *
     * @param string $prompt
     * @param array $conversationHistory
     * @return array
     */
    public function convertOpenAIResponse(array $openaiResponse): array
    {
        $choice = $openaiResponse['choices'][0] ?? [];
        $usage = $openaiResponse['usage'] ?? [];
        
        return [
            'model' => $openaiResponse['model'] ?? $this->model,
            'response' => $choice['message']['content'] ?? 'Resposta não disponível',
            'done' => true,
            'created_at' => now()->toIso8601String(),
            'tokens_input' => $usage['prompt_tokens'] ?? null,
            'tokens_output' => $usage['completion_tokens'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
            '_openai_id' => $openaiResponse['id'] ?? null,
            '_openai_object' => $openaiResponse['object'] ?? null,
        ];
    }

    /**
     * Atualiza o contexto do cliente
     *
     * @param string $contextKey
     * @param string $prompt
     * @param array $result
     * @param array $conversationHistory
     * @return void
     */
    protected function updateClientContext(string $contextKey, string $prompt, array $result, array $conversationHistory): void
    {
        $newInteraction = [
            'prompt' => $prompt,
            'response' => $result['response'] ?? 'Resposta não disponível',
            'timestamp' => now()->toIso8601String(),
        ];

        $conversationHistory[] = $newInteraction;
        
        // Mantém apenas as últimas 20 interações para não sobrecarregar
        if (count($conversationHistory) > 20) {
            $conversationHistory = array_slice($conversationHistory, -20);
        }

        $clientContext = [
            'history' => $conversationHistory,
            'last_updated' => now()->toIso8601String(),
            'total_interactions' => count($conversationHistory),
        ];

        // Salva contexto por 24 horas
        Cache::put($contextKey, $clientContext, 86400);
    }

    /**
     * Verifica a saúde do serviço de IA (OpenAI)
     *
     * @return bool
     */
    public function healthCheck()
    {
        try {
            // Verifica se a API OpenAI está respondendo
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(5)->get($this->apiUrl . '/models');
            
            if (!$response->successful()) {
                Log::warning('API de IA não está respondendo', [
                    'status' => $response->status(),
                    'url' => $this->apiUrl . '/models'
                ]);
                return false;
            }
            
            $models = $response->json();
            
            // Verifica se o modelo configurado está disponível
            $modelAvailable = collect($models['data'] ?? [])->contains('id', $this->model);
            
            if (!$modelAvailable) {
                Log::warning('Modelo configurado não está disponível na API de IA', [
                    'configured_model' => $this->model,
                    'available_models' => collect($models['data'] ?? [])->pluck('id')->take(10)->toArray()
                ]);
                // Para OpenAI, não falha se o modelo não estiver na lista (pode ser novo)
                // return false;
            }
            
            Log::info('Health check da IA bem-sucedido', [
                'model' => $this->model,
                'service' => 'OpenAI'
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Falha no health check da IA', [
                'exception' => $e->getMessage(),
                'url' => $this->apiUrl . '/models'
            ]);
            return false;
        }
    }

    /**
     * Constrói parâmetros OpenAI com compatibilidade para diferentes modelos
     *
     * @param array $parameters
     * @param array $messages
     * @return array
     */
    public function buildOpenAIParams(array $parameters, array $messages): array
    {
        // Garantir que o modelo seja uma string válida
        $model = is_array($this->model) ? 'gpt-4.1-nano' : $this->model;
        
        $baseParams = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
        ];

        // Detecta se é um modelo GPT-5
        $isGpt5Model = is_string($model) && str_contains($model, 'gpt-5');
        
        if ($isGpt5Model) {
            // GPT-5 usa max_completion_tokens e precisa de mais espaço para raciocinar
            $baseParams['max_tokens'] = $parameters['max_tokens'] ?? $parameters['max_completion_tokens'] ?? 1024;
            
            // Garante que temperatura seja float
            $baseParams['temperature'] = (float) ($parameters['temperature'] ?? 0.7);
        } else {
            // GPT-3.5-turbo e outros modelos suportam todos os parâmetros padrão
            $baseParams['temperature'] = (float) ($parameters['temperature'] ?? 0.7);
            $baseParams['max_tokens'] = (int) ($parameters['max_tokens'] ?? 1000);
        }

        return $baseParams;
    }
} 