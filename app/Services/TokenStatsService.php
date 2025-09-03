<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TokenStatsService
{
    /**
     * Obtém estatísticas de tokens para uma API Key (ultra-rápido)
     */
    public function getStats(int $apiKeyId): array
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        
        return [
            'today' => [
                'tokens' => (int) Cache::get("tokens:daily:{$apiKeyId}:{$today}", 0),
                'requests' => (int) Cache::get("burst:{$apiKeyId}:" . floor(time() / 10), 0),
            ],
            'this_month' => [
                'tokens' => (int) Cache::get("tokens:monthly:{$apiKeyId}:{$thisMonth}", 0),
            ],
            'total' => [
                'tokens' => (int) Cache::get("tokens:total:{$apiKeyId}", 0),
            ],
            'rate_limits' => [
                'burst_remaining' => max(0, 8 - (int) Cache::get("burst:{$apiKeyId}:" . floor(time() / 10), 0)),
                'minute_remaining' => max(0, 30 - (int) Cache::get("minute:{$apiKeyId}:" . floor(time() / 60), 0)),
            ]
        ];
    }

    /**
     * Obtém top APIs por consumo (para dashboard)
     */
    public function getTopConsumers(int $limit = 10): array
    {
        // Implementar se necessário para dashboard admin
        return [];
    }

    /**
     * Reset contadores (para manutenção)
     */
    public function resetCounters(int $apiKeyId): bool
    {
        $patterns = [
            "tokens:daily:{$apiKeyId}:*",
            "tokens:monthly:{$apiKeyId}:*",
            "tokens:total:{$apiKeyId}",
            "burst:{$apiKeyId}:*",
            "minute:{$apiKeyId}:*",
        ];

        foreach ($patterns as $pattern) {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }

        return true;
    }
}

