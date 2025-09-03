<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Services\AntiFloodService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtém a chave da API do cabeçalho
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key não fornecida',
            ], 401);
        }

        // Busca a chave no banco de dados
        $keyModel = ApiKey::where('key', $apiKey)->first();

        if (!$keyModel) {
            return response()->json([
                'error' => 'API key inválida',
            ], 401);
        }

        // Verifica se a chave está ativa
        if ($keyModel->status !== 'active') {
            return response()->json([
                'error' => 'API key inativa ou revogada',
                'status' => $keyModel->status,
            ], 403);
        }

        // Verifica se a chave expirou
        if ($keyModel->expires_at && now()->isAfter($keyModel->expires_at)) {
            return response()->json([
                'error' => 'API key expirada',
                'expired_at' => $keyModel->expires_at,
            ], 403);
        }

        // Sistema Anti-Flood Inteligente
        $antiFlood = new AntiFloodService();
        $floodCheck = $antiFlood->canMakeRequest($keyModel->id, $this->estimateTokens($request));
        
        if (!$floodCheck['allowed']) {
            return response()->json([
                'error' => 'Requisição bloqueada por proteção anti-flood',
                'reason' => $floodCheck['reason'],
                'message' => $floodCheck['message'],
                'details' => $floodCheck
            ], 429);
        }

        // Verifica limites básicos de uso com cache Redis otimizado
        $this->checkRateLimits($keyModel);

        // Verifica se a API Key possui um plano ativo e se pode fazer requisições
        $activeAssignment = $keyModel->activePlan;
        
        if (!$activeAssignment) {
            return response()->json([
                'error' => 'API key não possui plano ativo',
                'code' => 'NO_ACTIVE_PLAN',
            ], 403);
        }

        // Verifica se o plano permite fazer requisições
        if (!$activeAssignment->canMakeRequest()) {
            return response()->json([
                'error' => 'Limite de tokens do plano excedido',
                'code' => 'PLAN_LIMIT_EXCEEDED',
                'daily_usage' => [
                    'input' => $activeAssignment->current_daily_input_tokens,
                    'output' => $activeAssignment->current_daily_output_tokens,
                    'limit_input' => $activeAssignment->plan->daily_input_tokens,
                    'limit_output' => $activeAssignment->plan->daily_output_tokens,
                ],
                'monthly_usage' => [
                    'input' => $activeAssignment->current_monthly_input_tokens,
                    'output' => $activeAssignment->current_monthly_output_tokens,
                    'limit_input' => $activeAssignment->plan->monthly_input_tokens,
                    'limit_output' => $activeAssignment->plan->monthly_output_tokens,
                ],
                'reset_daily' => now()->addDay()->startOfDay(),
                'reset_monthly' => now()->addMonth()->startOfDay(),
            ], 429);
        }

        // Atualiza o último uso da chave
        $keyModel->last_used_at = $now;
        $keyModel->save();

        // Adiciona a chave API e o plano ativo ao request para uso posterior
        $request->apiKey = $keyModel;
        $request->activePlan = $activeAssignment;

        return $next($request);
    }

    /**
     * Verifica rate limits usando cache Redis otimizado
     */
    private function checkRateLimits(ApiKey $keyModel): void
    {
        $now = now();
        $apiKeyId = $keyModel->id;
        
        // Chaves de cache para contadores
        $minuteKey = "rate_limit:minute:{$apiKeyId}:" . $now->format('Y-m-d-H-i');
        $hourKey = "rate_limit:hour:{$apiKeyId}:" . $now->format('Y-m-d-H');
        $dayKey = "rate_limit:day:{$apiKeyId}:" . $now->format('Y-m-d');
        
        // Incrementa contadores com TTL automático
        $minuteCount = Cache::increment($minuteKey, 1);
        if ($minuteCount === 1) Cache::expire($minuteKey, 60); // Expira em 60 segundos
        
        $hourCount = Cache::increment($hourKey, 1);
        if ($hourCount === 1) Cache::expire($hourKey, 3600); // Expira em 1 hora
        
        $dayCount = Cache::increment($dayKey, 1);
        if ($dayCount === 1) Cache::expire($dayKey, 86400); // Expira em 1 dia
        
        // Verifica limites
        if ($minuteCount > $keyModel->rate_limit_minute) {
            abort(429, json_encode([
                'error' => 'Limite de requisições por minuto excedido',
                'limit' => $keyModel->rate_limit_minute,
                'reset_at' => $now->copy()->addMinute()->startOfMinute(),
            ]));
        }
        
        if ($hourCount > $keyModel->rate_limit_hour) {
            abort(429, json_encode([
                'error' => 'Limite de requisições por hora excedido',
                'limit' => $keyModel->rate_limit_hour,
                'reset_at' => $now->copy()->addHour()->startOfHour(),
            ]));
        }
        
        if ($dayCount > $keyModel->rate_limit_day) {
            abort(429, json_encode([
                'error' => 'Limite de requisições por dia excedido',
                'limit' => $keyModel->rate_limit_day,
                'reset_at' => $now->copy()->addDay()->startOfDay(),
            ]));
        }
    }
}
