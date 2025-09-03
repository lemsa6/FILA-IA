<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SimpleApiKeyMiddleware
{
    /**
     * Handle an incoming request.
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

        // Buscar a chave API no banco de dados
        $apiKeyRecord = DB::table('api_keys')
            ->where('key', $apiKey)
            ->where('status', 'active')
            ->first();

        if (!$apiKeyRecord) {
            return response()->json([
                'error' => 'API key inválida ou inativa',
            ], 401);
        }

        $request->apiKey = $apiKeyRecord;

        return $next($request);
    }
}
