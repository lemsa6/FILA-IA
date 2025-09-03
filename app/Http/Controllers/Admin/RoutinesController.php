<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IAService;
use App\Services\IntelligentAIService;
use App\Services\ClientContextService;
use App\Models\ApiKey;
use App\Models\Request as GPTRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class RoutinesController extends Controller
{
    /**
     * Exibe o dashboard de rotinas
     */
    public function index()
    {
        return view('admin.routines.index');
    }

    /**
     * Teste completo do sistema GPT
     */
    public function testGPT(Request $request)
    {
        try {
            $iaService = app(IAService::class);
            
            // 1. Health Check
            $healthStatus = $iaService->healthCheck();
            
            if (!$healthStatus) {
                return response()->json([
                    'success' => false,
                    'error' => 'Serviço OpenAI GPT não está respondendo',
                    'tests' => [
                        'health_check' => false,
                        'api_test' => false,
                        'performance_test' => false
                    ]
                ], 503);
            }

            // 2. Teste de API
            $startTime = microtime(true);
            $testPrompt = "Responda apenas: 'Teste funcionando'";
            
            $response = $iaService->generateCompletion($testPrompt, [
                'temperature' => 0.1,
                'max_tokens' => 50
            ]);
            
            $apiResponseTime = (microtime(true) - $startTime) * 1000; // ms
            
            // 3. Teste de Performance
            $performanceResults = $this->runPerformanceTest($iaService);
            
            $result = [
                'success' => true,
                'message' => 'Teste GPT executado com sucesso',
                'tests' => [
                    'health_check' => true,
                    'api_test' => true,
                    'performance_test' => true
                ],
                'details' => [
                    'health_status' => $healthStatus,
                    'api_response' => $response['response'] ?? 'N/A',
                    'api_response_time' => round($apiResponseTime, 2) . ' ms',
                    'performance' => $performanceResults
                ],
                'timestamp' => now()->format('d/m/Y H:i:s')
            ];

            Log::info('Teste GPT executado com sucesso', [
                'user_id' => auth()->id(),
                'response_time' => $apiResponseTime,
                'performance' => $performanceResults
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erro ao executar teste GPT', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao executar teste: ' . $e->getMessage(),
                'tests' => [
                    'health_check' => false,
                    'api_test' => false,
                    'performance_test' => false
                ]
            ], 500);
        }
    }

    /**
     * Teste do sistema de cache inteligente
     */
    public function testIntelligentCache(Request $request)
    {
        try {
            $intelligentAIService = app(IntelligentAIService::class);
            $clientContextService = app(ClientContextService::class);
            
            // Busca uma API key para teste
            $apiKey = ApiKey::first();
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhuma API key encontrada para teste'
                ], 404);
            }

            $apiKeyId = $apiKey->id;
            $sessionId = 'test-session-' . time();

            // 1. Teste de definição de contexto base
            $baseContext = "Somos uma empresa de tecnologia especializada em IA e automação. 
            Oferecemos soluções personalizadas para empresas de todos os tamanhos. 
            Nossa equipe tem mais de 10 anos de experiência no mercado.";
            
            $contextSuccess = $intelligentAIService->setBaseContext($apiKeyId, $baseContext, [
                'domain' => 'tecnologia',
                'test' => true
            ]);

            // 2. Teste de geração com contexto
            $startTime = microtime(true);
            $response1 = $intelligentAIService->generateIntelligentResponse(
                'Quais serviços vocês oferecem?',
                $apiKeyId,
                $sessionId
            );
            $response1Time = (microtime(true) - $startTime) * 1000;

            // 3. Teste de segunda pergunta (deve usar contexto)
            $startTime = microtime(true);
            $response2 = $intelligentAIService->generateIntelligentResponse(
                'Qual é o tempo de experiência da equipe?',
                $apiKeyId,
                $sessionId
            );
            $response2Time = (microtime(true) - $startTime) * 1000;

            // 4. Teste de estatísticas
            $stats = $intelligentAIService->getCacheStats($apiKeyId);

            // 5. Limpeza do teste
            $intelligentAIService->removeBaseContext($apiKeyId);

            $result = [
                'success' => true,
                'message' => 'Teste de cache inteligente executado com sucesso',
                'tests' => [
                    'context_setup' => $contextSuccess,
                    'first_response' => !empty($response1),
                    'second_response' => !empty($response2),
                    'cache_stats' => !empty($stats)
                ],
                'details' => [
                    'context_setup' => $contextSuccess ? 'Sucesso' : 'Falha',
                    'first_response_time' => round($response1Time, 2) . ' ms',
                    'second_response_time' => round($response2Time, 2) . ' ms',
                    'cache_stats' => $stats,
                    'session_id' => $sessionId
                ],
                'timestamp' => now()->format('d/m/Y H:i:s')
            ];

            Log::info('Teste de cache inteligente executado com sucesso', [
                'user_id' => auth()->id(),
                'api_key_id' => $apiKeyId,
                'response_times' => [$response1Time, $response2Time]
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erro ao executar teste de cache inteligente', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ], 500);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao executar teste: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Status geral do sistema
     */
    public function systemStatus()
    {
        try {
            $iaService = app(IAService::class);
            $clientContextService = app(ClientContextService::class);

            // 1. Status do Ollama
            $iaHealthy = $iaService->healthCheck();
            
            // 2. Status do Redis
            $redisHealthy = $this->checkRedisHealth();
            
            // 3. Status do banco de dados
            $dbHealthy = $this->checkDatabaseHealth();
            
            // 4. Estatísticas gerais
            $stats = $this->getSystemStats();

            $overallHealth = $ollamaHealthy && $redisHealthy && $dbHealthy;

            return response()->json([
                'success' => true,
                'overall_health' => $overallHealth,
                'services' => [
                    'ollama' => [
                        'healthy' => $ollamaHealthy,
                        'model' => config('services.ollama.model'),
                        'url' => config('services.ollama.url')
                    ],
                    'redis' => [
                        'healthy' => $redisHealthy,
                        'host' => config('cache.stores.redis.host'),
                        'port' => config('cache.stores.redis.port')
                    ],
                    'database' => [
                        'healthy' => $dbHealthy,
                        'connection' => config('database.default'),
                        'host' => config('database.connections.mysql.host')
                    ]
                ],
                'statistics' => $stats,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do sistema', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executa teste de performance
     */
    protected function runPerformanceTest(IAService $iaService): array
    {
        $results = [];
        $prompts = [
            'Teste 1: Responda "OK"',
            'Teste 2: Responda "OK"',
            'Teste 3: Responda "OK"'
        ];

        $totalTime = 0;
        $successCount = 0;

        foreach ($prompts as $index => $prompt) {
            $startTime = microtime(true);
            
            try {
                $response = $iaService->generateCompletion($prompt, [
                    'temperature' => 0.1,
                    'max_tokens' => 10
                ]);
                
                $responseTime = (microtime(true) - $startTime) * 1000;
                $totalTime += $responseTime;
                $successCount++;
                
                $results["test_" . ($index + 1)] = [
                    'success' => true,
                    'response_time' => round($responseTime, 2) . ' ms',
                    'response' => $response['response'] ?? 'N/A'
                ];
            } catch (\Exception $e) {
                $results["test_" . ($index + 1)] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'total_tests' => count($prompts),
            'successful_tests' => $successCount,
            'success_rate' => round(($successCount / count($prompts)) * 100, 2) . '%',
            'average_response_time' => $successCount > 0 ? round($totalTime / $successCount, 2) . ' ms' : 'N/A',
            'total_time' => round($totalTime, 2) . ' ms',
            'details' => $results
        ];
    }

    /**
     * Verifica saúde do Redis
     */
    protected function checkRedisHealth(): bool
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            return $value === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verifica saúde do banco de dados
     */
    protected function checkDatabaseHealth(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtém estatísticas gerais do sistema
     */
    protected function getSystemStats(): array
    {
        try {
            $totalRequests = OllamaRequest::count();
            $pendingRequests = OllamaRequest::where('status', 'pending')->count();
            $completedRequests = OllamaRequest::where('status', 'completed')->count();
            $failedRequests = OllamaRequest::where('status', 'failed')->count();
            
            $totalApiKeys = ApiKey::count();
            $activeApiKeys = ApiKey::where('status', 'active')->count();

            return [
                'requests' => [
                    'total' => $totalRequests,
                    'pending' => $pendingRequests,
                    'completed' => $completedRequests,
                    'failed' => $failedRequests,
                    'success_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) . '%' : '0%'
                ],
                'api_keys' => [
                    'total' => $totalApiKeys,
                    'active' => $activeApiKeys
                ],
                'cache' => [
                    'driver' => config('cache.default'),
                    'session_driver' => config('session.driver')
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica o status do Redis
     */
    public function redisStatus()
    {
        try {
            $redis = Redis::connection();
            $redis->ping();
            
            return response()->json([
                'healthy' => true,
                'message' => 'Redis funcionando normalmente',
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do Redis', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'healthy' => false,
                'error' => 'Redis não está respondendo: ' . $e->getMessage(),
                'timestamp' => now()->format('d/m/Y H:i:s')
            ], 503);
        }
    }

    /**
     * Verifica o status do Database
     */
    public function databaseStatus()
    {
        try {
            DB::connection()->getPdo();
            
            return response()->json([
                'healthy' => true,
                'message' => 'Database funcionando normalmente',
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do Database', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'healthy' => false,
                'error' => 'Database não está respondendo: ' . $e->getMessage(),
                'timestamp' => now()->format('d/m/Y H:i:s')
            ], 503);
        }
    }
}
