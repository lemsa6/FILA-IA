<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class IntelligentAIService
{
    /**
     * Serviço de IA
     *
     * @var IAService
     */
    protected $iaService;

    /**
     * Serviço de contexto do cliente
     *
     * @var ClientContextService
     */
    protected $clientContextService;

    /**
     * Construtor
     */
    public function __construct(
        IAService $iaService,
        ClientContextService $clientContextService
    ) {
        $this->iaService = $iaService;
        $this->clientContextService = $clientContextService;
    }

    /**
     * Gera resposta com cache persistente inteligente
     *
     * @param string $prompt
     * @param string $apiKeyId
     * @param string|null $sessionId
     * @param array $parameters
     * @param array $cacheControl
     * @return array
     */
    public function generateIntelligentResponse(
        string $prompt,
        string $apiKeyId,
        ?string $sessionId = null,
        array $parameters = [],
        array $cacheControl = []
    ): array {
        try {
            // 1. Obtém ou cria sessão
            $session = $this->clientContextService->getOrCreateSession($apiKeyId, $sessionId);
            $sessionId = $session['session_id'];

            // 2. Obtém contexto base do produto/serviço
            $baseContext = $this->getBaseContext($apiKeyId);

            // 3. Obtém histórico da conversa
            $conversationHistory = $this->clientContextService->getConversationHistory($apiKeyId, $sessionId);

            // 4. Constrói prompt completo com contexto
            $fullPrompt = $this->buildFullPrompt($baseContext, $conversationHistory, $prompt);

            // 5. Gera resposta via IA (OpenAI GPT-5)
            $result = $this->iaService->generateCompletion(
                $fullPrompt,
                $parameters,
                $apiKeyId,
                $sessionId
            );

            // 6. Atualiza contexto da conversa
            $this->clientContextService->addInteraction($apiKeyId, $sessionId, $prompt, $result);

            // 7. Adiciona metadados de cache
            $result['_cache_info'] = [
                'base_context_used' => !empty($baseContext),
                'conversation_length' => count($conversationHistory),
                'session_id' => $sessionId,
                'cache_hit' => false
            ];

            return $result;

        } catch (Exception $e) {
            Log::error('Erro no serviço de IA inteligente', [
                'api_key_id' => $apiKeyId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Define o contexto base do produto/serviço para um cliente
     *
     * @param string $apiKeyId
     * @param string $baseContext
     * @param array $metadata
     * @return bool
     */
    public function setBaseContext(string $apiKeyId, string $baseContext, array $metadata = []): bool
    {
        try {
            $contextKey = "ai:client_{$apiKeyId}:base_context";
            
            $contextData = [
                'content' => $baseContext,
                'metadata' => $metadata,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'content_hash' => md5($baseContext),
                'content_length' => strlen($baseContext),
                'estimated_tokens' => $this->estimateTokens($baseContext)
            ];

            // Salva por 30 dias (contexto base é mais duradouro)
            Cache::put($contextKey, $contextData, 2592000);

            Log::info('Contexto base definido para cliente', [
                'api_key_id' => $apiKeyId,
                'content_length' => strlen($baseContext),
                'estimated_tokens' => $contextData['estimated_tokens']
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erro ao definir contexto base', [
                'api_key_id' => $apiKeyId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Obtém o contexto base do produto/serviço
     *
     * @param string $apiKeyId
     * @return string
     */
    public function getBaseContext(string $apiKeyId): string
    {
        $contextKey = "ai:client_{$apiKeyId}:base_context";
        $contextData = Cache::get($contextKey);

        if (!$contextData) {
            return '';
        }

        return $contextData['content'];
    }

    /**
     * Atualiza o contexto base do produto/serviço
     *
     * @param string $apiKeyId
     * @param string $newBaseContext
     * @param array $metadata
     * @return bool
     */
    public function updateBaseContext(string $apiKeyId, string $newBaseContext, array $metadata = []): bool
    {
        return $this->setBaseContext($apiKeyId, $newBaseContext, $metadata);
    }

    /**
     * Remove o contexto base do produto/serviço
     *
     * @param string $apiKeyId
     * @return bool
     */
    public function removeBaseContext(string $apiKeyId): bool
    {
        try {
            $contextKey = "ai:client_{$apiKeyId}:base_context";
            Cache::forget($contextKey);

            Log::info('Contexto base removido para cliente', [
                'api_key_id' => $apiKeyId
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erro ao remover contexto base', [
                'api_key_id' => $apiKeyId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Obtém informações sobre o contexto base
     *
     * @param string $apiKeyId
     * @return array|null
     */
    public function getBaseContextInfo(string $apiKeyId): ?array
    {
        $contextKey = "ai:client_{$apiKeyId}:base_context";
        return Cache::get($contextKey);
    }

    /**
     * Constrói prompt completo com contexto e histórico
     *
     * @param string $baseContext
     * @param array $conversationHistory
     * @param string $currentPrompt
     * @return string
     */
    protected function buildFullPrompt(string $baseContext, array $conversationHistory, string $currentPrompt): string
    {
        $fullPrompt = '';

        // 1. Adiciona contexto base se existir
        if (!empty($baseContext)) {
            $fullPrompt .= "=== CONTEXTO DO PRODUTO/SERVIÇO ===\n";
            $fullPrompt .= $baseContext . "\n\n";
        }

        // 2. Adiciona histórico da conversa se existir
        if (!empty($conversationHistory)) {
            $fullPrompt .= "=== HISTÓRICO DA CONVERSA ===\n";
            
            // Limita a últimas 10 interações para não sobrecarregar
            $recentHistory = array_slice($conversationHistory, -10);
            
            foreach ($recentHistory as $interaction) {
                $fullPrompt .= "Usuário: {$interaction['prompt']}\n";
                $fullPrompt .= "IA: {$interaction['response']}\n\n";
            }
        }

        // 3. Adiciona a pergunta atual
        $fullPrompt .= "=== PERGUNTA ATUAL ===\n";
        $fullPrompt .= $currentPrompt;

        return $fullPrompt;
    }

    /**
     * Estima o número de tokens (aproximação)
     *
     * @param string $text
     * @return int
     */
    protected function estimateTokens(string $text): int
    {
        // Aproximação: 1 token ≈ 4 caracteres para português
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Obtém estatísticas de uso do cache
     *
     * @param string $apiKeyId
     * @return array
     */
    public function getCacheStats(string $apiKeyId): array
    {
        $baseContextInfo = $this->getBaseContextInfo($apiKeyId);
        $sessions = $this->clientContextService->getActiveSessions($apiKeyId);

        $totalInteractions = 0;
        foreach ($sessions as $session) {
            $history = $this->clientContextService->getConversationHistory($apiKeyId, $session['session_id']);
            $totalInteractions += count($history);
        }

        return [
            'base_context' => $baseContextInfo ? [
                'has_context' => true,
                'content_length' => $baseContextInfo['content_length'],
                'estimated_tokens' => $baseContextInfo['estimated_tokens'],
                'last_updated' => $baseContextInfo['updated_at']
            ] : [
                'has_context' => false
            ],
            'sessions' => [
                'active_count' => count($sessions),
                'total_interactions' => $totalInteractions
            ],
            'cache_efficiency' => [
                'base_context_used' => !empty($baseContextInfo),
                'conversation_context_used' => $totalInteractions > 0
            ]
        ];
    }
}
