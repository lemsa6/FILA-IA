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
     * Envia uma requisição para a IA (OpenAI) com isolamento por cliente.
     *
     * Dois modos de operação:
     *   - Modo simples:  $prompt fornecido — constrói messages internamente com histórico de sessão.
     *   - Modo avançado: $messages fornecido — usa o array diretamente (tool calling multi-turno).
     *     Neste modo o cache de prompt é desabilitado pois as mensagens já incluem resultados
     *     de tools e não devem ser reutilizadas entre chamadas.
     *
     * @param string     $prompt     Texto do usuário (modo simples)
     * @param array      $parameters Parâmetros de geração (temperature, max_tokens)
     * @param string     $apiKeyId   ID da chave API para isolamento por cliente
     * @param string|null $sessionId ID da sessão para histórico (modo simples)
     * @param array|null  $messages  Array de mensagens OpenAI (modo avançado, sobrescreve prompt)
     * @param array       $tools     Definições de tools para function calling
     */
    public function generateCompletion(
        string $prompt,
        array $parameters = [],
        string $apiKeyId = null,
        ?string $sessionId = null,
        ?array $messages = null,
        array $tools = []
    ) {
        $useAdvancedMode = !empty($messages);

        // Cache de prompt apenas no modo simples (sem tools)
        $clientCacheKey = null;
        if (!$useAdvancedMode && empty($tools)) {
            $promptHash = md5($prompt . json_encode($parameters));
            $clientCacheKey = "gpt:client_{$apiKeyId}:prompt_{$promptHash}";

            $cachedResponse = Cache::get($clientCacheKey);
            if ($cachedResponse) {
                $cachedResponse['_cache_hit'] = true;
                return $cachedResponse;
            }
        }

        $clientContextKey = "gpt:client_{$apiKeyId}:context_{$sessionId}";

        return $this->circuitBreaker->execute(
            function () use ($prompt, $parameters, $apiKeyId, $sessionId, $clientCacheKey, $clientContextKey, $messages, $tools, $useAdvancedMode) {
                if ($useAdvancedMode) {
                    // Modo avançado: usa messages diretamente, sem histórico interno
                    $openaiMessages = $messages;
                } else {
                    // Modo simples: constrói messages a partir do prompt + histórico de sessão
                    $clientContext = Cache::get($clientContextKey, []);
                    $conversationHistory = $clientContext['history'] ?? [];
                    $openaiMessages = $this->buildOpenAIMessages($prompt, $conversationHistory);
                }

                // Prepara parâmetros OpenAI
                $defaultParams = $this->buildOpenAIParams($parameters, $openaiMessages, $tools);

                // Mescla parâmetros extras (sem sobrescrever os já processados)
                $parametersArray = is_array($parameters) ? $parameters : [];
                $filteredParams = array_diff_key($parametersArray, ['temperature' => '', 'max_tokens' => '']);
                $params = array_merge($defaultParams, $filteredParams);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->timeout(30)->post($this->apiUrl . '/chat/completions', $params);

                if ($response->successful()) {
                    $openaiResult = $response->json();

                    $result = $this->convertOpenAIResponse($openaiResult);

                    // Atualiza contexto de sessão apenas no modo simples
                    if (!$useAdvancedMode) {
                        $clientContext = Cache::get($clientContextKey, []);
                        $this->updateClientContext($clientContextKey, $prompt, $result, $clientContext['history'] ?? []);
                    }

                    // Cache de prompt apenas para respostas simples (não tool_calls)
                    if ($clientCacheKey && ($result['finish_reason'] ?? 'stop') !== 'tool_calls') {
                        Cache::put($clientCacheKey, $result, config('services.openai.cache.ttl', 3600));
                    }

                    if (Cache::get("circuit_breaker:gpt:state") === 'open') {
                        $this->circuitBreaker->reset();
                        Log::info('Serviço OpenAI recuperado, circuit breaker resetado automaticamente');
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
                        ])->timeout(12)->post($this->apiUrl . '/chat/completions', $fallbackParams);
                        
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
        
        // Adiciona histórico da conversa — últimas 3 trocas (6 mensagens)
        // Manter menos contexto reduz tokens enviados e acelera a resposta da OpenAI
        if (!empty($conversationHistory)) {
            $recentHistory = array_slice($conversationHistory, -3);
            
            foreach ($recentHistory as $interaction) {
                // Trunca mensagens grandes para reduzir tokens
                $userContent = strlen($interaction['prompt']) > 300 ? 
                    substr($interaction['prompt'], 0, 300) . '...' : 
                    $interaction['prompt'];
                    
                $assistantContent = strlen($interaction['response']) > 600 ? 
                    substr($interaction['response'], 0, 600) . '...' : 
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
     * Converte resposta OpenAI para formato interno.
     *
     * Dois casos:
     *   finish_reason = "tool_calls" → retorna tool_calls para execução pelo cliente (WP-COMPLETO).
     *   finish_reason = "stop"       → retorna resposta textual final.
     */
    public function convertOpenAIResponse(array $openaiResponse): array
    {
        $choice       = $openaiResponse['choices'][0] ?? [];
        $usage        = $openaiResponse['usage'] ?? [];
        $finishReason = $choice['finish_reason'] ?? 'stop';
        $message      = $choice['message'] ?? [];

        $base = [
            'model'         => $openaiResponse['model'] ?? $this->model,
            'finish_reason' => $finishReason,
            'done'          => $finishReason !== 'tool_calls',
            'created_at'    => now()->toIso8601String(),
            'tokens_input'  => $usage['prompt_tokens'] ?? null,
            'tokens_output' => $usage['completion_tokens'] ?? null,
            'total_tokens'  => $usage['total_tokens'] ?? null,
            '_openai_id'    => $openaiResponse['id'] ?? null,
        ];

        if ($finishReason === 'tool_calls') {
            // Retorna as tool_calls e a mensagem completa do assistente para o cliente
            // montar o próximo turno (role=assistant + role=tool)
            $base['response']          = null;
            $base['tool_calls']        = $message['tool_calls'] ?? [];
            $base['assistant_message'] = $message; // mensagem completa para reinjetar no array
        } else {
            $base['response'] = $message['content'] ?? 'Resposta não disponível';
        }

        return $base;
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
     * Constrói parâmetros OpenAI com compatibilidade para diferentes modelos.
     *
     * @param array $parameters Parâmetros de geração (temperature, max_tokens)
     * @param array $messages   Array de mensagens no formato OpenAI
     * @param array $tools      Definições de tools para function calling (opcional)
     */
    public function buildOpenAIParams(array $parameters, array $messages, array $tools = []): array
    {
        $model = is_array($this->model) ? 'gpt-4.1-nano' : $this->model;

        $baseParams = [
            'model'    => $model,
            'messages' => $messages,
            'stream'   => false,
        ];

        $isGpt5Model = is_string($model) && str_contains($model, 'gpt-5');

        if ($isGpt5Model) {
            $baseParams['max_tokens']  = $parameters['max_tokens'] ?? $parameters['max_completion_tokens'] ?? 1024;
            $baseParams['temperature'] = (float) ($parameters['temperature'] ?? 0.7);
        } else {
            $baseParams['temperature'] = (float) ($parameters['temperature'] ?? 0.7);
            $baseParams['max_tokens']  = (int) ($parameters['max_tokens'] ?? 600);
        }

        // Adiciona tools quando fornecidas (ativa function calling nativo)
        if (!empty($tools)) {
            $baseParams['tools']       = $tools;
            $baseParams['tool_choice'] = 'auto';
        }

        return $baseParams;
    }
} 