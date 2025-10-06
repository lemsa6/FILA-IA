@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header com Status e Atualização -->
            <div class="mb-6">
                <div class="flex items-center justify-end">
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Última atualização</div>
                            <div class="text-lg font-semibold text-gray-700" id="last-update">{{ now()->format('H:i:s') }}</div>
                        </div>
                        <button onclick="refreshDashboard()" class="bg-yellow-500 hover:bg-yellow-600 text-black px-4 py-2 rounded-lg font-medium transition-colors">
                            Atualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Métricas Principais (KPIs) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total de Requisições -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="total-requests">{{ $totalRequests ?? 0 }}</div>
                            <div class="text-sm text-gray-600 font-medium">Total de Requisições</div>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <div class="w-6 h-6 bg-yellow-500 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Chaves API Ativas -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="api-keys-count">{{ $apiKeysCount ?? 0 }}</div>
                            <div class="text-sm text-gray-600 font-medium">Chaves API Ativas</div>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <div class="w-6 h-6 bg-yellow-500 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Em Processamento -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="processing-requests">{{ $processingRequests ?? 0 }}</div>
                            <div class="text-sm text-gray-600 font-medium">Em Processamento</div>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <div class="w-6 h-6 bg-yellow-500 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Cache Hit Rate -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="cache-hit-rate">{{ $performanceStats['cache_hit_rate'] ?? 0 }}%</div>
                            <div class="text-sm text-gray-600 font-medium">Cache Hit Rate</div>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <div class="w-6 h-6 bg-yellow-500 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout Principal em 2 Colunas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Coluna Principal (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Status dos Serviços (Simplificado) -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Status dos Serviços</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="w-3 h-3 rounded-full mx-auto mb-2" id="gpt-status-indicator"></div>
                                    <div class="text-sm font-medium text-gray-900">OpenAI GPT</div>
                                    <div class="text-xs text-gray-500">IA Externa</div>
                                </div>
                                <div class="text-center">
                                    <div class="w-3 h-3 rounded-full mx-auto mb-2" id="redis-status-indicator"></div>
                                    <div class="text-sm font-medium text-gray-900">Redis</div>
                                    <div class="text-xs text-gray-500">Cache & Fila</div>
                                </div>
                                <div class="text-center">
                                    <div class="w-3 h-3 rounded-full mx-auto mb-2" id="database-status-indicator"></div>
                                    <div class="text-sm font-medium text-gray-900">Database</div>
                                    <div class="text-xs text-gray-500">MySQL</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Solicitações por Dia -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Solicitações por Dia (Últimos 30 Dias)</h3>
                            <div class="h-64">
                                <canvas id="requestsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Performance -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Performance das Requisições</h3>
                            <div class="h-64">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Filas -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Status das Filas</h3>
                            <div class="h-64">
                                <canvas id="queueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral (1/3) -->
                <div class="space-y-6">
                    
                    <!-- Gestão Rápida (Corrigido) -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Gestão Rápida</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.apikeys.index') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                                    Gerenciar Chaves API
                                </a>
                                <a href="{{ route('admin.routines.index') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                                    Rotinas de Sistema
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas de Tokens -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Estatísticas de Tokens</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total de Tokens:</span>
                                    <span class="font-semibold text-gray-900" id="total-tokens">{{ $tokenStats['total'] >= 1000 ? round($tokenStats['total'] / 1000, 0) . 'k' : $tokenStats['total'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tokens Entrada:</span>
                                    <span class="font-semibold text-yellow-600" id="total-input-tokens">{{ $tokenStats['total_input'] >= 1000 ? round($tokenStats['total_input'] / 1000, 0) . 'k' : $tokenStats['total_input'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tokens Saída:</span>
                                    <span class="font-semibold text-yellow-600" id="total-output-tokens">{{ $tokenStats['total_output'] >= 1000 ? round($tokenStats['total_output'] / 1000, 0) . 'k' : $tokenStats['total_output'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Média por Requisição:</span>
                                    <span class="font-semibold text-gray-900" id="avg-tokens">{{ $tokenStats['average'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tokens Hoje:</span>
                                    <span class="font-semibold text-gray-900" id="today-tokens">{{ $tokenStats['today'] >= 1000 ? round($tokenStats['today'] / 1000, 0) . 'k' : $tokenStats['today'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas de Cache -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Cache GPT</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Hit Rate:</span>
                                    <span class="font-semibold text-yellow-600" id="cache-hit-rate-detail">{{ $cacheStats['hit_rate'] ?? 0 }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Hits:</span>
                                    <span class="font-semibold text-gray-900" id="cache-hits">{{ number_format($cacheStats['total_hits'] ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Misses:</span>
                                    <span class="font-semibold text-gray-900" id="cache-misses">{{ number_format($cacheStats['total_misses'] ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Economia de Tokens:</span>
                                    <span class="font-semibold text-yellow-600" id="token-savings">~{{ (($cacheStats['total_hits'] ?? 0) * 150) >= 1000 ? round((($cacheStats['total_hits'] ?? 0) * 150) / 1000, 0) . 'k' : (($cacheStats['total_hits'] ?? 0) * 150) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Performance</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tempo Médio:</span>
                                    <span class="font-semibold text-gray-900" id="avg-processing-time">{{ $performanceStats['avg_processing_time'] ?? 0 }}ms</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Taxa de Sucesso:</span>
                                    <span class="font-semibold text-yellow-600" id="success-rate-detail">{{ $performanceStats['success_rate'] ?? 0 }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Requisições Completadas:</span>
                                    <span class="font-semibold text-gray-900" id="completed-requests">{{ number_format($completedRequests ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Requisições Falhadas:</span>
                                    <span class="font-semibold text-red-600" id="failed-requests">{{ number_format($failedRequests ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Sistema -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Informações do Sistema</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Versão:</span>
                                    <span class="font-medium text-gray-900">{{ app()->version() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ambiente:</span>
                                    <span class="font-medium text-gray-900">{{ config('app.env') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Última atualização:</span>
                                    <span class="font-medium text-gray-900" id="system-update">{{ now()->format('H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let performanceChart, queueChart, requestsChart;

        // Função para atualizar o dashboard
        function refreshDashboard() {
            document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
            document.getElementById('system-update').textContent = new Date().toLocaleTimeString();
            
            updateServiceStatus();
            updateCharts();
            updateTokenStats();
        }

        // Função para atualizar status dos serviços
        function updateServiceStatus() {
            // Status do GPT
            fetch('/admin/test-ai/status')
                .then(response => response.json())
                .then(data => {
                    const indicator = document.getElementById('gpt-status-indicator');
                    if (indicator) {
                        indicator.className = `w-3 h-3 rounded-full mx-auto mb-2 ${data.healthy ? 'bg-green-500' : 'bg-red-500'}`;
                    }
                })
                .catch(() => {
                    const indicator = document.getElementById('gpt-status-indicator');
                    if (indicator) indicator.className = 'w-3 h-3 rounded-full mx-auto mb-2 bg-red-500';
                });

            // Status do Redis
            fetch('/admin/routines/redis-status')
                .then(response => response.json())
                .then(data => {
                    const indicator = document.getElementById('redis-status-indicator');
                    if (indicator) {
                        indicator.className = `w-3 h-3 rounded-full mx-auto mb-2 ${data.healthy ? 'bg-green-500' : 'bg-red-500'}`;
                    }
                })
                .catch(() => {
                    const indicator = document.getElementById('redis-status-indicator');
                    if (indicator) indicator.className = 'w-3 h-3 rounded-full mx-auto mb-2 bg-red-500';
                });

            // Status do Database
            fetch('/admin/routines/database-status')
                .then(response => response.json())
                .then(data => {
                    const indicator = document.getElementById('database-status-indicator');
                    if (indicator) {
                        indicator.className = `w-3 h-3 rounded-full mx-auto mb-2 ${data.healthy ? 'bg-green-500' : 'bg-red-500'}`;
                    }
                })
                .catch(() => {
                    const indicator = document.getElementById('database-status-indicator');
                    if (indicator) indicator.className = 'w-3 h-3 rounded-full mx-auto mb-2 bg-red-500';
                });
        }

        // Função para atualizar gráficos
        function updateCharts() {
            // Dados REAIS do backend
            const requestsPerHour = @json($performanceStats['requests_per_hour'] ?? []);
            
            // Verificar se há dados
            if (!requestsPerHour || requestsPerHour.length === 0) {
                // Mostrar mensagem quando não há dados
                const performanceChartElement = document.getElementById('performanceChart');
                if (performanceChartElement) {
                    performanceChartElement.parentElement.innerHTML = `
                        <div class="flex items-center justify-center h-64 text-gray-500">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <p class="text-lg font-medium">Nenhuma requisição nas últimas 24h</p>
                                <p class="text-sm">Faça algumas requisições para ver o gráfico</p>
                            </div>
                        </div>
                    `;
                }
                return;
            }
            
            const performanceData = {
                labels: requestsPerHour.map(item => String(item.hour).padStart(2, '0') + ':00'),
                datasets: [{
                    label: 'Requisições por Hora',
                    data: requestsPerHour.map(item => item.count),
                    borderColor: '#EAB308',
                    backgroundColor: 'rgba(234, 179, 8, 0.1)',
                    tension: 0.4
                }]
            };

            const queueData = {
                labels: ['Default', 'High', 'Low', 'Failed'],
                datasets: [{
                    label: 'Jobs na Fila',
                    data: [5, 2, 1, 0],
                    backgroundColor: [
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(0, 0, 0, 0.8)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            };

            // Atualiza gráfico de performance
            if (performanceChart) {
                performanceChart.data = performanceData;
                performanceChart.update();
            }

            // Atualiza gráfico de filas
            if (queueChart) {
                queueChart.data = queueData;
                queueChart.update();
            }
        }

        // Função para atualizar estatísticas de tokens
        function updateTokenStats() {
            // Aqui você pode implementar a lógica para buscar estatísticas reais de tokens
            // Por enquanto, usa valores estáticos
        }

        // Inicializa gráficos ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Solicitações por Dia
            const requestsCtx = document.getElementById('requestsChart').getContext('2d');
            
            // Dados reais do backend ou dados de exemplo
            const requestsData = @json($requestsByDay ?? []);
            const labels = requestsData.map(item => item.date);
            const counts = requestsData.map(item => item.count);
            
            requestsChart = new Chart(requestsCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Requisições',
                        data: counts,
                        backgroundColor: 'rgba(234, 179, 8, 0.8)',
                        borderColor: '#EAB308',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });

            // Gráfico de Performance
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            performanceChart = new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: requestsPerHour.map(item => String(item.hour).padStart(2, '0') + ':00'),
                    datasets: [{
                        label: 'Requisições por Hora',
                        data: requestsPerHour.map(item => item.count),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });

            // Gráfico de Filas
            const queueCtx = document.getElementById('queueChart').getContext('2d');
            queueChart = new Chart(queueCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Default', 'High', 'Low', 'Failed'],
                    datasets: [{
                        label: 'Jobs na Fila',
                        data: [5, 2, 1, 0],
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#EF4444'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            updateServiceStatus();
            
            // Atualiza a cada 30 segundos
            setInterval(updateServiceStatus, 30000);
            setInterval(updateCharts, 30000);
        });
    </script>
@endsection
