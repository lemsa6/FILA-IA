<?php

use App\Http\Controllers\Api\RequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas públicas
Route::post('/v1/authenticate', function () {
    return response()->json(['message' => 'API key válida'], 200);
});

// Rota de teste sem middleware
Route::get('/v1/test', function () {
    return response()->json(['message' => 'Rota de teste funcionando', 'timestamp' => now()]);
});

// Rotas protegidas por API key
Route::middleware(\App\Http\Middleware\FastApiKeyMiddleware::class)->prefix('v1')->group(function () {
    // Rotas para requisições
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::get('/requests', [RequestController::class, 'index']);
    
    // Rotas para gerenciar sessões e contexto
    Route::get('/sessions', function (Request $request) {
        $clientContextService = app(\App\Services\ClientContextService::class);
        $sessions = $clientContextService->getActiveSessions($request->apiKey->id);
        
        return response()->json([
            'sessions' => $sessions,
            'total' => count($sessions)
        ]);
    });
    
    Route::get('/sessions/{sessionId}/history', function (Request $request, string $sessionId) {
        $clientContextService = app(\App\Services\ClientContextService::class);
        $history = $clientContextService->getConversationHistory($request->apiKey->id, $sessionId);
        
        return response()->json([
            'session_id' => $sessionId,
            'history' => $history,
            'total_interactions' => count($history)
        ]);
    });

    // Rotas para gerenciar contexto base (produto/serviço)
    Route::post('/context/base', [RequestController::class, 'setBaseContext']);
    Route::get('/context/base', [RequestController::class, 'getBaseContext']);
    Route::put('/context/base', [RequestController::class, 'updateBaseContext']);
    Route::delete('/context/base', [RequestController::class, 'removeBaseContext']);
    
    // Rota para estatísticas de cache
    Route::get('/cache/stats', [RequestController::class, 'getCacheStats']);
    
    // Rota para estatísticas rápidas de tokens (novo sistema ultra-rápido)
    Route::get('/stats/fast', [RequestController::class, 'fastStats']);
}); 