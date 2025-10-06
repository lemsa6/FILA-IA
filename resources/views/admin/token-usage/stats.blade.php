@extends('layouts.app')

@section('title', 'Estatísticas de Uso de Tokens')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Mensagens de Sucesso -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Cabeçalho da Página -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Estatísticas de Uso de Tokens</h1>
                <p class="text-gray-600 mt-1">Visão geral do consumo de tokens e custos</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.token-usage.index') }}" class="btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Ver Logs
                </a>
                <a href="{{ route('admin.token-usage.alerts') }}" class="btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Alertas
                </a>
            </div>
        </div>

        <!-- Estatísticas Principais -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_requests'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Total de Requisições</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_input_tokens'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Tokens de Entrada</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_output_tokens'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Tokens de Saída</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">R$ {{ number_format($stats['total_cost_brl'] ?? 0, 4, ',', '.') }}</div>
                        <div class="text-sm text-gray-600">Custo Total (BRL)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos REAIS e Análises -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfico de Evolução Mensal REAL -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Evolução Mensal (Dados Reais)</h3>
                <div class="h-64">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Distribuição por API Key REAL -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔑 Top 5 APIs por Custo (Dados Reais)</h3>
                <div class="h-64">
                    <canvas id="apiChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Estatísticas Detalhadas -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas Detalhadas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Médias</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tokens de Entrada:</span>
                            <span class="font-medium">{{ number_format($stats['avg_input_tokens'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tokens de Saída:</span>
                            <span class="font-medium">{{ number_format($stats['avg_output_tokens'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Custo por Requisição:</span>
                            <span class="font-medium">R$ {{ number_format($stats['avg_cost_brl'] ?? 0, 4, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Totais</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total de Tokens:</span>
                            <span class="font-medium">{{ number_format($stats['total_tokens'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Custo USD:</span>
                            <span class="font-medium">$ {{ number_format($stats['total_cost_usd'] ?? 0, 6) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Custo BRL:</span>
                            <span class="font-medium">R$ {{ number_format($stats['total_cost_brl'] ?? 0, 4, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Informações</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Período:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Última Atualização:</span>
                            <span class="font-medium">{{ now()->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 📈 Gráfico de Evolução Mensal REAL
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    
    // Dados reais do backend
    const monthlyData = @json($monthlyStats);
    
    // Verificar se há dados
    if (monthlyData.length === 0) {
        // Mostrar mensagem quando não há dados
        document.getElementById('monthlyChart').parentElement.innerHTML = `
            <div class="flex items-center justify-center h-64 text-gray-500">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-lg font-medium">Nenhum dado disponível</p>
                    <p class="text-sm">Faça algumas requisições para ver os gráficos</p>
                </div>
            </div>
        `;
        return;
    }
    
    const monthLabels = monthlyData.map(item => {
        const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        return months[item.month - 1] + '/' + item.year;
    });
    const inputTokens = monthlyData.map(item => item.input_tokens || 0);
    const outputTokens = monthlyData.map(item => item.output_tokens || 0);
    const costs = monthlyData.map(item => parseFloat(item.cost_brl || 0));
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Tokens Entrada',
                data: inputTokens,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Tokens Saída',
                data: outputTokens,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Custo (R$)',
                data: costs,
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Últimos 6 meses - Dados Reais' }
            },
            scales: {
                y: { type: 'linear', display: true, position: 'left', beginAtZero: true },
                y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
            }
        }
    });

    // 🔑 Gráfico de APIs REAL
    const apiCtx = document.getElementById('apiChart').getContext('2d');
    
    // Dados reais das APIs
    const apiData = @json($apiStats);
    
    // Verificar se há dados
    if (apiData.length === 0) {
        // Mostrar mensagem quando não há dados
        document.getElementById('apiChart').parentElement.innerHTML = `
            <div class="flex items-center justify-center h-64 text-gray-500">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <p class="text-lg font-medium">Nenhuma API utilizada</p>
                    <p class="text-sm">Crie chaves de API e faça requisições</p>
                </div>
            </div>
        `;
        return;
    }
    
    const apiLabels = apiData.map(item => item.api_key ? item.api_key.name : 'API #' + item.api_key_id);
    const apiCosts = apiData.map(item => parseFloat(item.cost_brl || 0));
    const apiRequests = apiData.map(item => item.requests || 0);
    
    new Chart(apiCtx, {
        type: 'doughnut',
        data: {
            labels: apiLabels,
            datasets: [{
                label: 'Custo (R$)',
                data: apiCosts,
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Distribuição Real de Custos por API' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const requests = apiRequests[context.dataIndex] || 0;
                            return label + ': R$ ' + value.toFixed(4) + ' (' + requests + ' req)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection

