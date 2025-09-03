<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClientContextService
{
    /**
     * Cria ou recupera uma sessão para um cliente
     *
     * @param string $apiKeyId
     * @param string|null $sessionId
     * @return array
     */
    public function getOrCreateSession(string $apiKeyId, ?string $sessionId = null): array
    {
        if (!$sessionId) {
            $sessionId = $this->generateSessionId();
        }

        $sessionKey = "gpt:client_{$apiKeyId}:session_{$sessionId}";
        
        $session = Cache::get($sessionKey, [
            'session_id' => $sessionId,
            'api_key_id' => $apiKeyId,
            'created_at' => now()->toIso8601String(),
            'last_activity' => now()->toIso8601String(),
            'total_requests' => 0,
            'conversation_history' => [],
        ]);

        // Atualiza última atividade
        $session['last_activity'] = now()->toIso8601String();
        $session['total_requests']++;
        
        // Salva sessão por 24 horas
        Cache::put($sessionKey, $session, 86400);
        
        return $session;
    }

    /**
     * Adiciona uma interação ao histórico da sessão
     *
     * @param string $apiKeyId
     * @param string $sessionId
     * @param string $prompt
     * @param array $response
     * @return void
     */
    public function addInteraction(string $apiKeyId, string $sessionId, string $prompt, array $response): void
    {
        $sessionKey = "gpt:client_{$apiKeyId}:session_{$sessionId}";
        $session = Cache::get($sessionKey, []);
        
        $interaction = [
            'prompt' => $prompt,
            'response' => $response['response'] ?? 'Resposta não disponível',
            'timestamp' => now()->toIso8601String(),
            'processing_time' => $response['processing_time'] ?? null,
            'model' => $response['model'] ?? 'unknown',
        ];

        $session['conversation_history'][] = $interaction;
        $session['last_activity'] = now()->toIso8601String();
        
        // Limita o histórico a 50 interações para não sobrecarregar
        if (count($session['conversation_history']) > 50) {
            $session['conversation_history'] = array_slice($session['conversation_history'], -50);
        }
        
        Cache::put($sessionKey, $session, 86400);
    }

    /**
     * Obtém o histórico de conversa de uma sessão
     *
     * @param string $apiKeyId
     * @param string $sessionId
     * @return array
     */
    public function getConversationHistory(string $apiKeyId, string $sessionId): array
    {
        $sessionKey = "gpt:client_{$apiKeyId}:session_{$sessionId}";
        $session = Cache::get($sessionKey, []);
        
        return $session['conversation_history'] ?? [];
    }

    /**
     * Lista todas as sessões ativas de um cliente
     *
     * @param string $apiKeyId
     * @return array
     */
    public function getActiveSessions(string $apiKeyId): array
    {
        $pattern = "gpt:client_{$apiKeyId}:session_*";
        $sessions = [];
        
        // Busca por padrão no cache (implementação simplificada)
        // Em produção, use Redis SCAN ou similar
        $keys = Cache::get("gpt:client_{$apiKeyId}:session_keys", []);
        
        foreach ($keys as $sessionId) {
            $session = $this->getSession($apiKeyId, $sessionId);
            if ($session) {
                $sessions[] = $session;
            }
        }
        
        return $sessions;
    }

    /**
     * Obtém uma sessão específica
     *
     * @param string $apiKeyId
     * @param string $sessionId
     * @return array|null
     */
    public function getSession(string $apiKeyId, string $sessionId): ?array
    {
        $sessionKey = "gpt:client_{$apiKeyId}:session_{$sessionId}";
        $session = Cache::get($sessionKey);
        
        if (!$session) {
            return null;
        }
        
        // Verifica se a sessão não expirou
        $lastActivity = \Carbon\Carbon::parse($session['last_activity']);
        if ($lastActivity->diffInHours(now()) > 24) {
            Cache::forget($sessionKey);
            return null;
        }
        
        return $session;
    }

    /**
     * Gera um ID único para a sessão
     *
     * @return string
     */
    protected function generateSessionId(): string
    {
        return 'sess_' . uniqid() . '_' . time();
    }

    /**
     * Limpa sessões antigas de um cliente
     *
     * @param string $apiKeyId
     * @return int
     */
    public function cleanupOldSessions(string $apiKeyId): int
    {
        $cleaned = 0;
        $keys = Cache::get("gpt:client_{$apiKeyId}:session_keys", []);
        
        foreach ($keys as $sessionId) {
            $session = $this->getSession($apiKeyId, $sessionId);
            if (!$session) {
                $cleaned++;
                // Remove da lista de chaves
                $keys = array_diff($keys, [$sessionId]);
            }
        }
        
        Cache::put("gpt:client_{$apiKeyId}:session_keys", $keys, 86400);
        
        return $cleaned;
    }
}
