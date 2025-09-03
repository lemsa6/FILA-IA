<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AntiFloodService
{
    /**
     * Configurações de proteção anti-flood
     */
    private const FLOOD_DETECTION = [
        // Janelas de tempo para detecção
        'windows' => [
            'burst' => 10,      // 10 segundos - rajadas
            'short' => 60,      // 1 minuto - uso intenso
            'medium' => 300,    // 5 minutos - uso sustentado
            'long' => 3600,     // 1 hora - uso prolongado
        ],
        
        // Limites por janela (requisições)
        'limits' => [
            'burst' => 5,       // Máx 5 req em 10s
            'short' => 20,      // Máx 20 req em 1min
            'medium' => 100,    // Máx 100 req em 5min
            'long' => 500,      // Máx 500 req em 1h
        ],
        
        // Limites de tokens por período
        'token_budget' => [
            'short' => 10000,   // 10k tokens em 1min
            'medium' => 50000,  // 50k tokens em 5min
            'long' => 200000,   // 200k tokens em 1h
        ],
        
        // Penalidades por violação
        'penalties' => [
            'burst' => 30,      // Block 30s por burst
            'short' => 300,     // Block 5min por uso intenso
            'medium' => 900,    // Block 15min por uso sustentado
            'long' => 3600,     // Block 1h por uso prolongado
        ]
    ];

    /**
     * Verifica se a API Key pode fazer requisição
     */
    public function canMakeRequest(string $apiKeyId, int $estimatedTokens = 1000): array
    {
        // Verifica se está bloqueada
        $blockKey = "flood_block:{$apiKeyId}";
        $blockInfo = Cache::get($blockKey);
        
        if ($blockInfo) {
            return [
                'allowed' => false,
                'reason' => 'flood_blocked',
                'blocked_until' => $blockInfo['until'],
                'violation_type' => $blockInfo['type'],
                'message' => "API bloqueada por flood até " . date('H:i:s', $blockInfo['until'])
            ];
        }

        // Verifica todas as janelas de tempo
        $violations = $this->checkAllWindows($apiKeyId, $estimatedTokens);
        
        if (!empty($violations)) {
            // Aplica penalidade pela violação mais severa
            $severestViolation = $this->getSeverestViolation($violations);
            $this->applyPenalty($apiKeyId, $severestViolation);
            
            return [
                'allowed' => false,
                'reason' => 'flood_detected',
                'violations' => $violations,
                'penalty_applied' => $severestViolation,
                'message' => "Flood detectado: {$severestViolation['type']}"
            ];
        }

        // Registra a requisição em todas as janelas
        $this->recordRequest($apiKeyId, $estimatedTokens);
        
        return [
            'allowed' => true,
            'reason' => 'within_limits',
            'current_usage' => $this->getCurrentUsage($apiKeyId),
            'message' => 'Requisição permitida'
        ];
    }

    /**
     * Verifica todas as janelas de tempo
     */
    private function checkAllWindows(string $apiKeyId, int $estimatedTokens): array
    {
        $violations = [];
        $now = time();
        
        foreach (self::FLOOD_DETECTION['windows'] as $window => $seconds) {
            // Verifica limites de requisições
            $requestKey = "flood_req:{$window}:{$apiKeyId}";
            $currentRequests = (int) Cache::get($requestKey, 0);
            
            if ($currentRequests >= self::FLOOD_DETECTION['limits'][$window]) {
                $violations[] = [
                    'type' => "request_{$window}",
                    'window' => $window,
                    'current' => $currentRequests,
                    'limit' => self::FLOOD_DETECTION['limits'][$window],
                    'severity' => $this->getSeverity($window)
                ];
            }
            
            // Verifica orçamento de tokens (exceto burst)
            if ($window !== 'burst' && isset(self::FLOOD_DETECTION['token_budget'][$window])) {
                $tokenKey = "flood_tokens:{$window}:{$apiKeyId}";
                $currentTokens = (int) Cache::get($tokenKey, 0);
                $tokenLimit = self::FLOOD_DETECTION['token_budget'][$window];
                
                if (($currentTokens + $estimatedTokens) > $tokenLimit) {
                    $violations[] = [
                        'type' => "token_{$window}",
                        'window' => $window,
                        'current' => $currentTokens,
                        'estimated' => $estimatedTokens,
                        'limit' => $tokenLimit,
                        'severity' => $this->getSeverity($window) + 1 // Tokens são mais severos
                    ];
                }
            }
        }
        
        return $violations;
    }

    /**
     * Registra uma requisição em todas as janelas
     */
    private function recordRequest(string $apiKeyId, int $actualTokens): void
    {
        $now = time();
        
        foreach (self::FLOOD_DETECTION['windows'] as $window => $seconds) {
            // Incrementa contador de requisições
            $requestKey = "flood_req:{$window}:{$apiKeyId}";
            $count = Cache::increment($requestKey, 1);
            if ($count === 1) {
                Cache::expire($requestKey, $seconds);
            }
            
            // Incrementa contador de tokens (exceto burst)
            if ($window !== 'burst') {
                $tokenKey = "flood_tokens:{$window}:{$apiKeyId}";
                $tokenCount = Cache::increment($tokenKey, $actualTokens);
                if ($tokenCount === $actualTokens) {
                    Cache::expire($tokenKey, $seconds);
                }
            }
        }
    }

    /**
     * Aplica penalidade por flood
     */
    private function applyPenalty(string $apiKeyId, array $violation): void
    {
        $penalty = self::FLOOD_DETECTION['penalties'][$violation['window']];
        $until = time() + $penalty;
        
        $blockInfo = [
            'type' => $violation['type'],
            'window' => $violation['window'],
            'until' => $until,
            'violation' => $violation,
            'applied_at' => time()
        ];
        
        Cache::put("flood_block:{$apiKeyId}", $blockInfo, $penalty);
        
        Log::warning('Anti-flood penalty applied', [
            'api_key_id' => $apiKeyId,
            'violation' => $violation,
            'penalty_seconds' => $penalty,
            'blocked_until' => date('Y-m-d H:i:s', $until)
        ]);
    }

    /**
     * Obtém a violação mais severa
     */
    private function getSeverestViolation(array $violations): array
    {
        return collect($violations)->sortByDesc('severity')->first();
    }

    /**
     * Calcula severidade baseada na janela
     */
    private function getSeverity(string $window): int
    {
        $severities = [
            'burst' => 4,   // Mais severo
            'short' => 3,
            'medium' => 2,
            'long' => 1     // Menos severo
        ];
        
        return $severities[$window] ?? 0;
    }

    /**
     * Obtém uso atual em todas as janelas
     */
    private function getCurrentUsage(string $apiKeyId): array
    {
        $usage = [];
        
        foreach (self::FLOOD_DETECTION['windows'] as $window => $seconds) {
            $requestKey = "flood_req:{$window}:{$apiKeyId}";
            $tokenKey = "flood_tokens:{$window}:{$apiKeyId}";
            
            $usage[$window] = [
                'requests' => [
                    'current' => (int) Cache::get($requestKey, 0),
                    'limit' => self::FLOOD_DETECTION['limits'][$window],
                    'percentage' => round((Cache::get($requestKey, 0) / self::FLOOD_DETECTION['limits'][$window]) * 100, 1)
                ]
            ];
            
            if (isset(self::FLOOD_DETECTION['token_budget'][$window])) {
                $usage[$window]['tokens'] = [
                    'current' => (int) Cache::get($tokenKey, 0),
                    'limit' => self::FLOOD_DETECTION['token_budget'][$window],
                    'percentage' => round((Cache::get($tokenKey, 0) / self::FLOOD_DETECTION['token_budget'][$window]) * 100, 1)
                ];
            }
        }
        
        return $usage;
    }

    /**
     * Remove penalidade manualmente (para suporte)
     */
    public function unblockApiKey(string $apiKeyId, string $reason = 'manual_unblock'): bool
    {
        $blockKey = "flood_block:{$apiKeyId}";
        $removed = Cache::forget($blockKey);
        
        if ($removed) {
            Log::info('Anti-flood block removed manually', [
                'api_key_id' => $apiKeyId,
                'reason' => $reason,
                'removed_by' => 'system'
            ]);
        }
        
        return $removed;
    }

    /**
     * Obtém estatísticas de flood para monitoramento
     */
    public function getFloodStats(): array
    {
        // Implementar estatísticas globais se necessário
        return [
            'active_blocks' => $this->getActiveBlocks(),
            'recent_violations' => $this->getRecentViolations(),
        ];
    }

    private function getActiveBlocks(): int
    {
        // Contar bloqueios ativos (implementar se necessário)
        return 0;
    }

    private function getRecentViolations(): array
    {
        // Obter violações recentes (implementar se necessário)
        return [];
    }
}

