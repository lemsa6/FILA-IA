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
        // Sistema simplificado: usar dados reais dos requests GPT
        $query = GPTRequest::with(['apiKey'])->where('status', 'completed');

        // Filtros
        if ($request->filled('api_key_id')) {
            $query->where('api_key_id', $request->api_key_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Estatísticas reais baseadas nos requests processados
        $stats = [
            'total_requests' => GPTRequest::where('status', 'completed')->count(),
            'total_input_tokens' => GPTRequest::where('status', 'completed')->sum('tokens_input'),
            'total_output_tokens' => GPTRequest::where('status', 'completed')->sum('tokens_output'),
            'total_tokens' => GPTRequest::where('status', 'completed')->sum(DB::raw('tokens_input + tokens_output')),
            'avg_processing_time' => GPTRequest::where('status', 'completed')->avg('processing_time'),
            'total_cost_usd' => GPTRequest::where('status', 'completed')->sum('cost_usd'),
            'total_cost_brl' => GPTRequest::where('status', 'completed')->sum('cost_brl'),
            'avg_cost_usd' => GPTRequest::where('status', 'completed')->avg('cost_usd'),
            'avg_cost_brl' => GPTRequest::where('status', 'completed')->avg('cost_brl'),
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
        // Sistema simplificado: usar dados reais dos requests GPT
        $query = GPTRequest::where('status', 'completed');

        // Filtros opcionais
        if ($request->filled('api_key_id')) {
            $query->where('api_key_id', $request->api_key_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
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

        // Dados para gráfico por período (últimos 30 dias)
        $dailyStats = GPTRequest::selectRaw('DATE(created_at) as date, SUM(tokens_input + tokens_output) as tokens, COUNT(*) as requests')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Dados para gráfico por modelo
        $modelStats = GPTRequest::selectRaw('model, COUNT(*) as count, SUM(tokens_input + tokens_output) as tokens')
            ->where('status', 'completed')
            ->whereNotNull('model')
            ->groupBy('model')
            ->orderBy('tokens', 'desc')
            ->limit(5)
            ->get();

        // Lista de API Keys para filtro
        $apiKeys = ApiKey::where('status', 'active')->get();

        return view('admin.token-usage.stats', compact(
            'stats',
            'dailyStats',
            'modelStats',
            'apiKeys'
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