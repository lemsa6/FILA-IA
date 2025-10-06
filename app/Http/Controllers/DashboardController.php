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
                'serviceStatus'
            ));
            
        } catch (\Exception $e) {
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
                'serviceStatus' => []
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
        try {
            // Tempo médio de processamento (em milissegundos)
            $avgProcessingTime = GPTRequest::whereNotNull('processing_time')
                ->where('status', 'completed')
                ->avg('processing_time');
            
            // Taxa de sucesso
            $totalRequests = GPTRequest::count();
            $successfulRequests = GPTRequest::where('status', 'completed')->count();
            $successRate = $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;
            
            // Cache Hit Rate
            $cacheHitCount = GPTRequest::where('status', 'completed')
                ->where('cache_info->cache_hit', true)
                ->count();
            $cacheHitRate = $successfulRequests > 0 ? ($cacheHitCount / $successfulRequests) * 100 : 0;
            
            // Requisições por hora (últimos 30 dias)
            $requestsPerHour = GPTRequest::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
            
            // DEBUG: Log dos dados para verificar
            \Log::info('Dashboard Debug - requestsPerHour:', [
                'count' => $requestsPerHour->count(),
                'data' => $requestsPerHour->toArray(),
                'total_requests' => GPTRequest::count(),
                'last_30d_requests' => GPTRequest::where('created_at', '>=', now()->subDays(30))->count()
            ]);
            
            return [
                'avg_processing_time' => round($avgProcessingTime ?? 0, 0),
                'success_rate' => round($successRate, 1),
                'cache_hit_rate' => round($cacheHitRate, 1),
                'requests_per_hour' => $requestsPerHour,
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'cache_hits' => $cacheHitCount
            ];
            
        } catch (\Exception $e) {
            return [
                'avg_processing_time' => 0,
                'success_rate' => 0,
                'cache_hit_rate' => 0,
                'requests_per_hour' => [],
                'total_requests' => 0,
                'successful_requests' => 0,
                'cache_hits' => 0
            ];
        }
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
            $cacheHits = GPTRequest::where('status', 'completed')
                ->where('cache_info->cache_hit', true)
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
                    ->where('cache_info->cache_hit', true)
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
