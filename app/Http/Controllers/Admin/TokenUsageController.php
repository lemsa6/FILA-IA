<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TokenUsageLog;
use App\Models\ApiKey;
use App\Models\Request as GPTRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TokenUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 📅 Filtro padrão: últimos 30 dias se não especificado
        $startDate = $request->filled('start_date') ? $request->start_date : now()->subDays(30)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');
        
        // Sistema simplificado: usar dados reais dos requests GPT
        $query = GPTRequest::with(['apiKey'])->where('status', 'completed');

        // Aplicar filtro de período (sempre aplicado)
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);

        // Filtros adicionais
        if ($request->filled('api_key_id')) {
            $query->where('api_key_id', $request->api_key_id);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // 📊 Estatísticas baseadas nos mesmos filtros aplicados
        $statsQuery = GPTRequest::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
            
        if ($request->filled('api_key_id')) {
            $statsQuery->where('api_key_id', $request->api_key_id);
        }
        
        $stats = [
            'total_requests' => $statsQuery->count(),
            'total_input_tokens' => $statsQuery->sum('tokens_input'),
            'total_output_tokens' => $statsQuery->sum('tokens_output'),
            'total_tokens' => $statsQuery->sum(DB::raw('tokens_input + tokens_output')),
            'avg_processing_time' => $statsQuery->avg('processing_time'),
            'total_cost_usd' => $statsQuery->sum('cost_usd'),
            'total_cost_brl' => $statsQuery->sum('cost_brl'),
            'avg_cost_usd' => $statsQuery->avg('cost_usd'),
            'avg_cost_brl' => $statsQuery->avg('cost_brl'),
            'period_start' => $startDate,
            'period_end' => $endDate,
        ];

        // APIs disponíveis para filtro
        $apiKeys = ApiKey::select('id', 'name')->where('status', 'active')->get();

        return view('admin.token-usage.index', compact(
            'logs',
            'stats',
            'apiKeys'
        ));
    }

    /**
     * Estatísticas de uso de tokens.
     */
    public function stats(Request $request)
    {
        // 📅 Filtro padrão: últimos 6 meses para análises mais focadas
        $startDate = $request->filled('start_date') ? $request->start_date : now()->subMonths(6)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');
        
        // Sistema com dados reais dos requests GPT - período focado
        $query = GPTRequest::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        // Filtros adicionais
        if ($request->filled('api_key_id')) {
            $query->where('api_key_id', $request->api_key_id);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_requests,
            SUM(tokens_input) as total_input_tokens,
            SUM(tokens_output) as total_output_tokens,
            SUM(tokens_input + tokens_output) as total_tokens,
            AVG(tokens_input) as avg_input_tokens,
            AVG(tokens_output) as avg_output_tokens,
            AVG(processing_time) as avg_processing_time,
            SUM(cost_usd) as total_cost_usd,
            SUM(cost_brl) as total_cost_brl,
            AVG(cost_usd) as avg_cost_usd,
            AVG(cost_brl) as avg_cost_brl
        ')->first();

        // 📈 Dados REAIS para gráfico mensal (últimos 6 meses)
        $monthlyStats = GPTRequest::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(tokens_input) as input_tokens,
            SUM(tokens_output) as output_tokens,
            SUM(cost_brl) as cost_brl,
            COUNT(*) as requests
        ')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // 📊 Dados REAIS para gráfico por API Key (top 5)
        $apiStats = GPTRequest::with('apiKey')
            ->selectRaw('
                api_key_id,
                COUNT(*) as requests,
                SUM(cost_brl) as cost_brl,
                SUM(tokens_input + tokens_output) as total_tokens
            ')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('api_key_id')
            ->orderBy('cost_brl', 'desc')
            ->limit(5)
            ->get();

        // Lista de API Keys para filtro
        $apiKeys = ApiKey::where('status', 'active')->get();

        return view('admin.token-usage.stats', compact(
            'stats',
            'monthlyStats',
            'apiStats',
            'apiKeys',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Alertas de uso excessivo - Sistema simplificado.
     */
    public function alerts()
    {
        // Sistema simplificado: alertas baseados em uso de tokens, não custos
        $alerts = [];
        
        // Buscar APIs com uso elevado de tokens (últimas 24h)
        $highUsageApis = GPTRequest::select('api_key_id', DB::raw('SUM(tokens_input + tokens_output) as total_tokens'), DB::raw('COUNT(*) as request_count'))
            ->with('apiKey')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('api_key_id')
            ->having('total_tokens', '>', 100000) // Mais de 100k tokens em 24h
            ->get();

        foreach ($highUsageApis as $usage) {
            if ($usage->total_tokens > 500000) { // Mais de 500k tokens
                $alerts[] = [
                    'alert_level' => 'critical',
                    'title' => 'Uso Crítico de Tokens',
                    'message' => "Consumiu " . number_format($usage->total_tokens) . " tokens nas últimas 24h ({$usage->request_count} requisições)",
                    'api_key_name' => $usage->apiKey->name ?? 'API Key #' . $usage->api_key_id,
                    'api_key_id' => $usage->api_key_id,
                ];
            } else {
                $alerts[] = [
                    'alert_level' => 'warning',
                    'title' => 'Uso Elevado de Tokens',
                    'message' => "Consumiu " . number_format($usage->total_tokens) . " tokens nas últimas 24h ({$usage->request_count} requisições)",
                    'api_key_name' => $usage->apiKey->name ?? 'API Key #' . $usage->api_key_id,
                    'api_key_id' => $usage->api_key_id,
                ];
            }
        }

        // Estatísticas gerais
        $totalAlerts = count($alerts);
        $criticalAlerts = count(array_filter($alerts, function($alert) {
            return $alert['alert_level'] === 'critical';
        }));
        $warningAlerts = $totalAlerts - $criticalAlerts;

        return view('admin.token-usage.alerts', compact(
            'alerts',
            'totalAlerts',
            'criticalAlerts',
            'warningAlerts'
        ));
    }
}