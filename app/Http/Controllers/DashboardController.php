<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Request as GPTRequest;
use App\Services\TokenStatsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard com estatísticas reais
     */
    public function index()
    {
        try {
            // Estatísticas de Chaves de API
            $apiKeysCount = ApiKey::where('status', 'active')->count();
            
            // Estatísticas de Requisições GPT
            $totalRequests = GPTRequest::count();
            $todayRequests = GPTRequest::whereDate('created_at', today())->count();
            $completedRequests = GPTRequest::where('status', 'completed')->count();
            $failedRequests = GPTRequest::where('status', 'failed')->count();

            // Números reais de período (30 dias e última semana)
            $requestsLast30Days = GPTRequest::where('created_at', '>=', now()->subDays(30))->count();
            $requestsLastWeek = GPTRequest::where('created_at', '>=', now()->subDays(7))->count();
            
            // Requisições em processamento (filas)
            $processingRequests = $this->getProcessingRequestsCount();
            
            // Estatísticas de performance
            $performanceStats = $this->getPerformanceStats();
            
            // Estatísticas de tokens (usando TokenStatsService)
            $tokenStats = $this->getTokenStats();
            
            // Dados de solicitações por dia
            $requestsByDay = $this->getRequestsByDay();
            
            // Estatísticas de cache
            $cacheStats = $this->getCacheStats();
            
            // Status dos serviços
            $serviceStatus = $this->getServiceStatus();
            
            return view('dashboard', compact(
                'apiKeysCount',
                'totalRequests', 
                'todayRequests',
                'completedRequests',
                'failedRequests',
                'processingRequests',
                'performanceStats',
                'tokenStats',
                'requestsByDay',
                'cacheStats',
                'serviceStatus',
                'requestsLast30Days',
                'requestsLastWeek'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar dashboard', ['exception' => $e->getMessage()]);
            // Em caso de erro, retorna valores padrão
            return view('dashboard', [
                'apiKeysCount' => 0,
                'totalRequests' => 0,
                'todayRequests' => 0,
                'completedRequests' => 0,
                'failedRequests' => 0,
                'processingRequests' => 0,
                'performanceStats' => [],
                'tokenStats' => [],
                'requestsByDay' => [],
                'cacheStats' => [],
                'serviceStatus' => [],
                'requestsLast30Days' => 0,
                'requestsLastWeek' => 0
            ]);
        }
    }
    
    /**
     * Obtém o número de requisições em processamento
     */
    private function getProcessingRequestsCount(): int
    {
        try {
            // Verifica filas do Redis (GPT)
            $redis = Redis::connection();
            $defaultQueue = $redis->lLen('queues:default');
            $gptQueue = $redis->lLen('queues:gpt-requests');
            
            // Também conta requisições com status 'processing'
            $processingInDB = GPTRequest::where('status', 'processing')->count();
            
            return $defaultQueue + $gptQueue + $processingInDB;
        } catch (\Exception $e) {
            return GPTRequest::where('status', 'processing')->count();
        }
    }
    
    /**
     * Obtém estatísticas de performance
     */
    private function getPerformanceStats(): array
    {
        // Cada métrica é calculada isoladamente: se uma falhar, as outras continuam
        // aparecendo normalmente (antes, uma única exceção zerava o painel inteiro).
        $totalRequests = GPTRequest::count();
        $successfulRequests = GPTRequest::where('status', 'completed')->count();

        $avgProcessingTime = 0;
        try {
            $avgProcessingTime = GPTRequest::whereNotNull('processing_time')
                ->where('status', 'completed')
                ->avg('processing_time');
        } catch (\Exception $e) {
            \Log::warning('Dashboard: falha ao calcular avg_processing_time', ['exception' => $e->getMessage()]);
        }

        $successRate = $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;

        // Cache Hit Rate — usa a coluna real `cache_hit` (antes apontava para
        // `cache_info->cache_hit`, coluna que nunca existiu na tabela e
        // derrubava esta função inteira via exceção silenciosa)
        $cacheHitCount = 0;
        try {
            $cacheHitCount = GPTRequest::where('status', 'completed')
                ->where('cache_hit', true)
                ->count();
        } catch (\Exception $e) {
            \Log::warning('Dashboard: falha ao calcular cache_hit_count', ['exception' => $e->getMessage()]);
        }
        $cacheHitRate = $successfulRequests > 0 ? ($cacheHitCount / $successfulRequests) * 100 : 0;

        // Requisições por hora do dia (agregado dos últimos 30 dias, para ter volume suficiente)
        $requestsPerHour = collect();
        try {
            $requestsPerHour = GPTRequest::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
        } catch (\Exception $e) {
            \Log::warning('Dashboard: falha ao calcular requests_per_hour', ['exception' => $e->getMessage()]);
        }

        return [
            'avg_processing_time' => round($avgProcessingTime ?? 0, 0),
            'success_rate' => round($successRate, 1),
            'cache_hit_rate' => round($cacheHitRate, 1),
            'requests_per_hour' => $requestsPerHour,
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'cache_hits' => $cacheHitCount
        ];
    }
    
    /**
     * Obtém estatísticas de tokens
     */
    private function getTokenStats(): array
    {
        try {
            // Estatísticas de tokens de entrada e saída
            $totalInputTokens = GPTRequest::where('status', 'completed')->sum('tokens_input');
            $totalOutputTokens = GPTRequest::where('status', 'completed')->sum('tokens_output');
            $totalTokens = $totalInputTokens + $totalOutputTokens;
            
            // Média de tokens por requisição
            $completedRequests = GPTRequest::where('status', 'completed')->count();
            $avgInputTokens = $completedRequests > 0 ? $totalInputTokens / $completedRequests : 0;
            $avgOutputTokens = $completedRequests > 0 ? $totalOutputTokens / $completedRequests : 0;
            
            // Tokens consumidos hoje
            $todayInputTokens = GPTRequest::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('tokens_input');
            $todayOutputTokens = GPTRequest::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('tokens_output');
            $todayTokens = $todayInputTokens + $todayOutputTokens;
            
            return [
                'total' => $totalTokens,
                'total_input' => $totalInputTokens,
                'total_output' => $totalOutputTokens,
                'average_input' => round($avgInputTokens, 0),
                'average_output' => round($avgOutputTokens, 0),
                'average' => round(($avgInputTokens + $avgOutputTokens), 0),
                'today' => $todayTokens,
                'today_input' => $todayInputTokens,
                'today_output' => $todayOutputTokens
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'total_input' => 0,
                'total_output' => 0,
                'average_input' => 0,
                'average_output' => 0,
                'average' => 0,
                'today' => 0,
                'today_input' => 0,
                'today_output' => 0
            ];
        }
    }

    /**
     * Obtém dados de solicitações por dia dos últimos 30 dias
     */
    private function getRequestsByDay(): array
    {
        try {
            $requestsByDay = [];
            
            // Gera array com os últimos 30 dias
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                
                // Conta requisições para este dia
                $count = GPTRequest::whereDate('created_at', $dateKey)->count();
                $completedCount = GPTRequest::whereDate('created_at', $dateKey)
                    ->where('status', 'completed')->count();
                
                $requestsByDay[] = [
                    'date' => $date->format('d/m'),
                    'count' => $count,
                    'completed' => $completedCount
                ];
            }
            
            return $requestsByDay;
        } catch (\Exception $e) {
            // Retorna dados de exemplo se houver erro
            $requestsByDay = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $requestsByDay[] = [
                    'date' => $date->format('d/m'),
                    'count' => rand(0, 20) // Dados de exemplo
                ];
            }
            return $requestsByDay;
        }
    }
    
    /**
     * Obtém estatísticas de cache
     */
    private function getCacheStats(): array
    {
        try {
            $totalRequests = GPTRequest::where('status', 'completed')->count();
            // Usa a coluna real `cache_hit` (antes: `cache_info->cache_hit`, que não existe)
            $cacheHits = GPTRequest::where('status', 'completed')
                ->where('cache_hit', true)
                ->count();
            $cacheMisses = $totalRequests - $cacheHits;
            $hitRate = $totalRequests > 0 ? ($cacheHits / $totalRequests) * 100 : 0;
            
            // Cache hits por dia (últimos 7 dias)
            $cacheByDay = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                
                $dayTotal = GPTRequest::whereDate('created_at', $dateKey)
                    ->where('status', 'completed')->count();
                $dayHits = GPTRequest::whereDate('created_at', $dateKey)
                    ->where('status', 'completed')
                    ->where('cache_hit', true)
                    ->count();
                $dayHitRate = $dayTotal > 0 ? ($dayHits / $dayTotal) * 100 : 0;
                
                $cacheByDay[] = [
                    'date' => $date->format('d/m'),
                    'hit_rate' => round($dayHitRate, 1),
                    'hits' => $dayHits,
                    'total' => $dayTotal
                ];
            }
            
            return [
                'hit_rate' => round($hitRate, 1),
                'total_hits' => $cacheHits,
                'total_misses' => $cacheMisses,
                'total_requests' => $totalRequests,
                'by_day' => $cacheByDay
            ];
        } catch (\Exception $e) {
            \Log::warning('Dashboard: falha ao calcular cache stats', ['exception' => $e->getMessage()]);
            return [
                'hit_rate' => 0,
                'total_hits' => 0,
                'total_misses' => 0,
                'total_requests' => 0,
                'by_day' => []
            ];
        }
    }
    
    /**
     * Obtém status dos serviços
     */
    private function getServiceStatus(): array
    {
        $status = [];
        
        try {
            // Status do Redis
            $redis = Redis::connection();
            $redis->ping();
            $status['redis'] = true;
        } catch (\Exception $e) {
            $status['redis'] = false;
        }
        
        try {
            // Status do Database
            DB::connection()->getPdo();
            $status['database'] = true;
        } catch (\Exception $e) {
            $status['database'] = false;
        }
        
        try {
            // Status do GPT/OpenAI (via cache para não sobrecarregar)
            $gptStatus = Cache::remember('gpt_status', 30, function () {
                $iaService = app(\App\Services\IAService::class);
                return $iaService->healthCheck();
            });
            $status['gpt'] = $gptStatus;
        } catch (\Exception $e) {
            $status['gpt'] = false;
        }
        
        return $status;
    }
    
    /**
     * API endpoint para dados em tempo real
     */
    public function apiData()
    {
        try {
            return response()->json([
                'total_requests' => GPTRequest::count(),
                'today_requests' => GPTRequest::whereDate('created_at', today())->count(),
                'processing_requests' => $this->getProcessingRequestsCount(),
                'performance_stats' => $this->getPerformanceStats(),
                'token_stats' => $this->getTokenStats(),
                'cache_stats' => $this->getCacheStats(),
                'service_status' => $this->getServiceStatus(),
                'timestamp' => now()->format('H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao carregar dados',
                'timestamp' => now()->format('H:i:s')
            ], 500);
        }
    }

    /**
     * Endpoint leve para o gráfico de ondas em tempo real (polling a cada 5s).
     * Retorna apenas números pequenos e rápidos de calcular — nada de queries pesadas.
     */
    public function liveMetrics()
    {
        try {
            $requestsLastMinute = GPTRequest::where('created_at', '>=', now()->subMinute())->count();
            $requestsLast5Seconds = GPTRequest::where('created_at', '>=', now()->subSeconds(5))->count();
            $processing = $this->getProcessingRequestsCount();

            $lastCompleted = GPTRequest::where('status', 'completed')
                ->whereNotNull('processing_time')
                ->orderBy('completed_at', 'desc')
                ->value('processing_time');

            return response()->json([
                'timestamp' => now()->format('H:i:s'),
                'requests_last_minute' => $requestsLastMinute,
                'requests_last_5s' => $requestsLast5Seconds,
                'processing' => $processing,
                'last_response_time_ms' => $lastCompleted ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'timestamp' => now()->format('H:i:s'),
                'requests_last_minute' => 0,
                'requests_last_5s' => 0,
                'processing' => 0,
                'last_response_time_ms' => 0,
            ]);
        }
    }

    /**
     * Formata números de tokens para exibição (ex: 164k, 1.2M)
     */
    private function formatTokenNumber($number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 0) . 'k';
        }
        return (string) $number;
    }
}
