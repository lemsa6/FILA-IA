<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
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

        // Verifica limites de uso (rate limiting)
        // Implementação simplificada - em produção, use Redis ou outro cache
        $now = now();
        $minuteStart = $now->copy()->startOfMinute();
        $hourStart = $now->copy()->startOfHour();
        $dayStart = $now->copy()->startOfDay();

        $minuteCount = $keyModel->requests()
            ->where('created_at', '>=', $minuteStart)
            ->count();

        if ($minuteCount >= $keyModel->rate_limit_minute) {
            return response()->json([
                'error' => 'Limite de requisições por minuto excedido',
                'limit' => $keyModel->rate_limit_minute,
                'reset_at' => $minuteStart->addMinute(),
            ], 429);
        }

        $hourCount = $keyModel->requests()
            ->where('created_at', '>=', $hourStart)
            ->count();

        if ($hourCount >= $keyModel->rate_limit_hour) {
            return response()->json([
                'error' => 'Limite de requisições por hora excedido',
                'limit' => $keyModel->rate_limit_hour,
                'reset_at' => $hourStart->addHour(),
            ], 429);
        }

        $dayCount = $keyModel->requests()
            ->where('created_at', '>=', $dayStart)
            ->count();

        if ($dayCount >= $keyModel->rate_limit_day) {
            return response()->json([
                'error' => 'Limite de requisições por dia excedido',
                'limit' => $keyModel->rate_limit_day,
                'reset_at' => $dayStart->addDay(),
            ], 429);
        }

        // Atualiza o último uso da chave
        $keyModel->last_used_at = $now;
        $keyModel->save();

        // Adiciona a chave API ao request para uso posterior
        $request->apiKey = $keyModel;

        return $next($request);
    }
}
