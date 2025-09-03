<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Services\AntiFloodService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class FastApiKeyMiddleware
{
    /**
     * Handle an incoming request - ULTRA RÁPIDO
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key obrigatória'], 401);
        }

        // ⚡ CACHE FIRST - Evita DB query na maioria dos casos
        $cacheKey = "fast_api_key:{$apiKey}";
        $keyData = Cache::get($cacheKey);
        
        if (!$keyData) {
            // Só vai no DB se não estiver em cache
            $keyModel = ApiKey::where('key', $apiKey)->first();
            
            if (!$keyModel || $keyModel->status !== 'active') {
                return response()->json(['error' => 'API key inválida'], 403);
            }
            
            // Cache por 5 minutos - reduz 90% das queries DB
            $keyData = [
                'id' => $keyModel->id,
                'name' => $keyModel->name,
                'status' => $keyModel->status
            ];
            Cache::put($cacheKey, $keyData, 300);
        }

        // ⚡ ANTI-FLOOD ULTRA-RÁPIDO (só Redis, zero DB)
        $floodCheck = $this->fastFloodCheck($keyData['id'], $request);
        if (!$floodCheck['allowed']) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $floodCheck['retry_after']
            ], 429);
        }

        // Adiciona dados mínimos ao request
        $request->merge(['api_key_id' => $keyData['id']]);
        
        // Adiciona o objeto apiKey ao request para compatibilidade com o controlador
        $apiKeyModel = ApiKey::find($keyData['id']);
        $request->apiKey = $apiKeyModel;
        
        return $next($request);
    }

    /**
     * Flood check ultra-rápido (apenas Redis)
     */
    private function fastFloodCheck(string $apiKeyId, Request $request): array
    {
        $now = time();
        
        // Apenas 2 verificações essenciais:
        // 1. Burst protection (10s)
        $burstKey = "burst:{$apiKeyId}:" . floor($now / 10);
        $burstCount = Cache::increment($burstKey, 1);
        if ($burstCount === 1) Cache::put($burstKey, 1, 15); // Corrigido: usando put com TTL em vez de expire
        
        if ($burstCount > 8) { // Max 8 req por 10s
            return ['allowed' => false, 'retry_after' => 10];
        }
        
        // 2. Sustained protection (1min)
        $minuteKey = "minute:{$apiKeyId}:" . floor($now / 60);
        $minuteCount = Cache::increment($minuteKey, 1);
        if ($minuteCount === 1) Cache::put($minuteKey, 1, 70); // Corrigido: usando put com TTL em vez de expire
        
        if ($minuteCount > 30) { // Max 30 req por minuto
            return ['allowed' => false, 'retry_after' => 60];
        }
        
        return ['allowed' => true];
    }
}

