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

            <!-- Volume Real de Requisições (30 dias / Última semana) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="requests-30d">{{ number_format($requestsLast30Days ?? 0) }}</div>
                            <div class="text-sm text-gray-600 font-medium">Requisições — Últimos 30 dias</div>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <div class="w-6 h-6 bg-blue-500 rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="requests-7d">{{ number_format($requestsLastWeek ?? 0) }}</div>
                            <div class="text-sm text-gray-600 font-medium">Requisições — Última semana</div>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <div class="w-6 h-6 bg-purple-500 rounded"></div>
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

                    <!-- Métricas Rápidas (sem gráficos duplicados) -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Métricas Rápidas</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $performanceStats['avg_processing_time'] ?? 0 }}ms</div>
                                    <div class="text-sm text-gray-600">Tempo Médio</div>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $performanceStats['success_rate'] ?? 0 }}%</div>
                                    <div class="text-sm text-gray-600">Taxa de Sucesso</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 📈 GRÁFICO: Requisições por Hora (Identifica Picos de Uso) -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">📈 Requisições por Hora do Dia</h3>
                                <span class="text-sm text-gray-500 bg-blue-50 px-2 py-1 rounded">Últimos 30 dias</span>
                            </div>
                            <div class="h-64">
                                <canvas id="performanceChart"></canvas>
                            </div>
                            <div class="mt-3 text-xs text-gray-500 text-center">
                                💡 Soma de todas as requisições dos últimos 30 dias, agrupadas pela hora do dia em que ocorreram
                            </div>
                        </div>
                    </div>

                    <!-- 🌊 GRÁFICO: Tempo Real (Ondas ao vivo, atualiza a cada 5s) -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">🌊 Atividade em Tempo Real</h3>
                                <span class="text-sm text-green-700 bg-green-50 px-2 py-1 rounded flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-500 rounded-full inline-block animate-pulse"></span>
                                    Ao vivo · a cada 5s
                                </span>
                            </div>
                            <div class="h-64">
                                <canvas id="liveWaveChart"></canvas>
                            </div>
                            <div class="mt-3 text-xs text-gray-500 text-center">
                                💡 Requisições no último minuto (janela deslizante) — atualizado automaticamente, sem precisar recarregar a página
                            </div>
                        </div>
                    </div>

                    <!-- 🔄 GRÁFICO: Status das Requisições (Monitora Saúde do Sistema) -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">🔄 Status das Requisições</h3>
                                <span class="text-sm text-gray-500 bg-green-50 px-2 py-1 rounded">Totais Acumulados</span>
                            </div>
                            <div class="h-64">
                                <canvas id="queueChart"></canvas>
                            </div>
                            <div class="mt-3 text-xs text-gray-500 text-center">
                                💡 Monitore a saúde do sistema - muitas falhas indicam problemas
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

    <!-- Chart.js Local (UMD) -->
    <script src="{{ asset('js/chart.umd.js') }}"></script>
    
    <script>
        let performanceChart, queueChart;

        // Função para atualizar o dashboard
        function refreshDashboard() {
            document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
            document.getElementById('system-update').textContent = new Date().toLocaleTimeString();
            
            updateServiceStatus();
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

        // Função para inicializar gráficos com dados reais
        function initializeCharts() {
            // 📈 GRÁFICO 1: Requisições por Hora (últimas 24h) - Para identificar picos de uso
            const performanceCanvas = document.getElementById('performanceChart');
            if (!performanceCanvas) {
                console.error('❌ Canvas performanceChart não encontrado!');
                return;
            }
            const performanceCtx = performanceCanvas.getContext('2d');
            
            // Dados reais do backend
            const requestsPerHour = @json($performanceStats['requests_per_hour'] ?? []);
            
            console.log('📊 Dados do gráfico:', requestsPerHour);
            
            if (requestsPerHour && requestsPerHour.length > 0) {
                const labels = requestsPerHour.map(item => String(item.hour).padStart(2, '0') + ':00');
                const data = requestsPerHour.map(item => item.count);
                
                console.log('📈 Labels:', labels);
                console.log('📈 Data:', data);
                
                performanceChart = new Chart(performanceCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Requisições por Hora',
                            data: data,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'Identifique picos de uso e planeje recursos'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0, 0, 0, 0.1)' },
                                title: { display: true, text: 'Número de Requisições' }
                            },
                            x: {
                                grid: { color: 'rgba(0, 0, 0, 0.1)' },
                                title: { display: true, text: 'Hora do Dia' }
                            }
                        }
                    }
                });
            } else {
                // Mostrar mensagem quando não há dados
                document.getElementById('performanceChart').parentElement.innerHTML = `
                    <div class="flex items-center justify-center h-64 text-gray-500">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-lg font-medium">Nenhuma requisição nos últimos 30 dias</p>
                            <p class="text-sm">Faça algumas requisições para ver o padrão de uso</p>
                        </div>
                    </div>
                `;
            }

            // 🔄 GRÁFICO 2: Status das Requisições - Para monitorar saúde do sistema
            const queueCanvas = document.getElementById('queueChart');
            if (!queueCanvas) {
                console.error('❌ Canvas queueChart não encontrado!');
                return;
            }
            const queueCtx = queueCanvas.getContext('2d');
            
            const statusData = {
                labels: ['✅ Completadas', '🔄 Processando', '⏳ Pendentes', '❌ Falhadas'],
                datasets: [{
                    data: [
                        @json($completedRequests ?? 0),
                        @json($processingRequests ?? 0),
                        @json(($totalRequests ?? 0) - ($completedRequests ?? 0) - ($failedRequests ?? 0) - ($processingRequests ?? 0)),
                        @json($failedRequests ?? 0)
                    ],
                    backgroundColor: [
                        '#10B981', // Verde - Completadas
                        '#3B82F6', // Azul - Processando
                        '#F59E0B', // Amarelo - Pendentes
                        '#EF4444'  // Vermelho - Falhadas
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            };

            queueChart = new Chart(queueCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Monitore a saúde do sistema em tempo real'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 🌊 GRÁFICO DE ONDAS EM TEMPO REAL — atualiza a cada 5 segundos via polling
        let liveWaveChart;
        const LIVE_WINDOW_SIZE = 30; // 30 pontos x 5s = últimos 2min30s de histórico visível

        function initializeLiveWaveChart() {
            const canvas = document.getElementById('liveWaveChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

            liveWaveChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array(LIVE_WINDOW_SIZE).fill(''),
                    datasets: [{
                        label: 'Requisições no último minuto',
                        data: Array(LIVE_WINDOW_SIZE).fill(0),
                        borderColor: '#10B981',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        tension: 0.45,
                        fill: true,
                        pointRadius: 0,
                        cubicInterpolationMode: 'monotone'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400, easing: 'easeOutQuad' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.parsed.y} requisição(ões)`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(0, 0, 0, 0.06)' }
                        },
                        x: { display: false }
                    }
                }
            });

            pollLiveMetrics();
            setInterval(pollLiveMetrics, 5000);
        }

        function pollLiveMetrics() {
            fetch('{{ route('dashboard.api.live') }}')
                .then(response => response.json())
                .then(data => {
                    if (!liveWaveChart) return;

                    const chartData = liveWaveChart.data.datasets[0].data;
                    chartData.push(data.requests_last_minute ?? 0);
                    chartData.shift();

                    const labels = liveWaveChart.data.labels;
                    labels.push(data.timestamp ?? '');
                    labels.shift();

                    liveWaveChart.update('none');

                    // Também sincroniza os cards "Em Processamento" e última atualização
                    const processingEl = document.getElementById('processing-requests');
                    if (processingEl && typeof data.processing === 'number') {
                        processingEl.textContent = data.processing;
                    }
                    document.getElementById('last-update').textContent = data.timestamp ?? new Date().toLocaleTimeString();
                })
                .catch(() => { /* silencioso: não interrompe o dashboard se a rede falhar por um instante */ });
        }

        // Função para atualizar estatísticas de tokens
        function updateTokenStats() {
            // Aqui você pode implementar a lógica para buscar estatísticas reais de tokens
            // Por enquanto, usa valores estáticos
        }

        // Inicializa dashboard ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            updateServiceStatus();
            initializeCharts();
            initializeLiveWaveChart();
            
            // Atualiza a cada 30 segundos
            setInterval(updateServiceStatus, 30000);
        });
    </script>
@endsection
