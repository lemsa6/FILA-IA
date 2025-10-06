@extends('layouts.app')

@section('title', 'Rotinas - Monitoramento do Sistema')

@section('content')

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Status Geral do Sistema -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Status Geral do Sistema</h3>
                            <p class="text-gray-600">Monitoramento em tempo real dos serviços</p>
                        </div>
                        <button id="refresh-status" class="bg-black text-white px-3 py-1.5 rounded-lg font-medium text-sm transition-all duration-300 ease-in-out hover:bg-gray-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Atualizar Status
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white rounded-xl p-6 text-center border border-gray-200 shadow-sm">
                            <div class="flex justify-center mb-3">
                                <div class="w-6 h-6 rounded-full bg-gray-300" id="gpt-status"></div>
                            </div>
                            <div class="text-sm text-gray-700 font-medium">OpenAI GPT</div>
                            <div class="text-xs text-gray-600 mt-1">IA Externa</div>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center border border-gray-200 shadow-sm">
                            <div class="flex justify-center mb-3">
                                <div class="w-6 h-6 rounded-full bg-gray-300" id="redis-status"></div>
                            </div>
                            <div class="text-sm text-gray-700 font-medium">Redis</div>
                            <div class="text-xs text-gray-600 mt-1">Cache & Fila</div>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center border border-gray-200 shadow-sm">
                            <div class="flex justify-center mb-3">
                                <div class="w-6 h-6 rounded-full bg-gray-300" id="database-status"></div>
                            </div>
                            <div class="text-sm text-gray-700 font-medium">Database</div>
                            <div class="text-xs text-gray-600 mt-1">MySQL</div>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center border border-gray-200 shadow-sm">
                            <div class="flex justify-center mb-3">
                                <div class="w-6 h-6 rounded-full bg-gray-300" id="overall-status"></div>
                            </div>
                            <div class="text-sm text-gray-700 font-medium">Geral</div>
                            <div class="text-xs text-gray-600 mt-1">Sistema</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards de Testes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <!-- Teste GPT -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Teste GPT</h3>
                                <p class="text-gray-600">Testa a API OpenAI GPT, health check e performance</p>
                            </div>

                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3" id="gpt-health-indicator"></span>
                                    <span class="text-sm font-medium text-gray-700">Health Check</span>
                                </div>
                                <span class="text-xs text-gray-500">Status do serviço</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3" id="gpt-api-indicator"></span>
                                    <span class="text-sm font-medium text-gray-700">Teste de API</span>
                                </div>
                                <span class="text-xs text-gray-500">Comunicação</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3" id="gpt-performance-indicator"></span>
                                    <span class="text-sm font-medium text-gray-700">Teste de Performance</span>
                                </div>
                                <span class="text-xs text-gray-500">Velocidade</span>
                            </div>
                        </div>
                        
                        <button id="test-gpt-btn" class="bg-yellow-400 text-black px-3 py-2 rounded-lg font-medium text-sm w-full transition-all duration-300 ease-in-out hover:bg-yellow-500 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:ring-opacity-50">
                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Executar Teste GPT
                        </button>
                    </div>
                </div>

                <!-- Teste de IA -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Teste de IA</h3>
                                <p class="text-gray-600">Testa o OpenAI GPT-4.1-nano com prompts reais</p>
                            </div>

                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3 bg-gray-400"></span>
                                    <span class="text-sm font-medium text-gray-700">Modelo Configurado</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ config('services.openai.model', 'gpt-4.1-nano') }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3 bg-gray-400"></span>
                                    <span class="text-sm font-medium text-gray-700">Status do Circuit Breaker</span>
                                </div>
                                <span class="text-xs text-gray-500">Monitorado</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3 bg-gray-400"></span>
                                    <span class="text-sm font-medium text-gray-700">Cache Inteligente</span>
                                </div>
                                <span class="text-xs text-gray-500">Ativo</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('admin.test-ai.index') }}" class="bg-black text-white px-3 py-2 rounded-lg font-medium text-sm w-full text-center inline-flex items-center justify-center transition-all duration-300 ease-in-out hover:bg-gray-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Acessar Teste de IA
                        </a>
                    </div>
                </div>

                <!-- Teste Cache Inteligente -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Teste Cache Inteligente</h3>
                                <p class="text-gray-600">Testa o sistema de cache persistente e contexto</p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3" id="cache-context-indicator"></span>
                                    <span class="text-sm font-medium text-gray-700">Setup de Contexto</span>
                                </div>
                                <span class="text-xs text-gray-500">Base inicial</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3" id="cache-response1-indicator"></span>
                                    <span class="text-sm font-medium text-gray-700">Primeira Resposta</span>
                                </div>
                                <span class="text-xs text-gray-500">Cache miss</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3" id="cache-response2-indicator"></span>
                                    <span class="text-sm font-medium text-gray-700">Segunda Resposta</span>
                                </div>
                                <span class="text-xs text-gray-500">Cache hit</span>
                            </div>
                        </div>
                        
                        <button id="test-cache-btn" class="bg-black text-white px-3 py-2 rounded-lg font-medium text-sm w-full transition-all duration-300 ease-in-out hover:bg-gray-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50">
                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            Executar Teste Cache
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resultados dos Testes -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Resultados dos Testes</h3>
                    <div id="test-results" class="space-y-4">
                        <div class="text-center text-gray-500 py-8">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-600 mb-2">Nenhum teste executado</p>
                            <p class="text-gray-500">Execute um dos testes acima para ver os resultados</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas do Sistema -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Estatísticas do Sistema</h3>
                    <div id="system-stats" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-500 mb-2" id="total-requests">...</div>
                            <div class="text-sm text-gray-600">Total de Requisições</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-500 mb-2" id="success-rate">...</div>
                            <div class="text-sm text-gray-600">Taxa de Sucesso</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-500 mb-2" id="avg-response-time">...</div>
                            <div class="text-sm text-gray-600">Tempo Médio de Resposta</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para atualizar indicadores de status
        function updateIndicator(elementId, status) {
            const element = document.getElementById(elementId);
            if (element) {
                element.className = `w-3 h-3 rounded-full mr-3 ${status === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            }
        }

        // Função para atualizar status geral do sistema
        function refreshSystemStatus() {
            // Status do GPT
            fetch('/admin/test-ai/status')
                .then(response => response.json())
                .then(data => {
                    const gptStatus = document.getElementById('gpt-status');
                    if (gptStatus) {
                        gptStatus.className = `w-6 h-6 rounded-full ${data.healthy ? 'bg-green-500' : 'bg-red-500'}`;
                    }
                })
                .catch(() => {
                    const gptStatus = document.getElementById('gpt-status');
                    if (gptStatus) {
                        gptStatus.className = 'w-6 h-6 rounded-full bg-red-500';
                    }
                });

            // Status do Redis
            fetch('/admin/routines/redis-status')
                .then(response => response.json())
                .then(data => {
                    const redisStatus = document.getElementById('redis-status');
                    if (redisStatus) {
                        redisStatus.className = `w-6 h-6 rounded-full ${data.healthy ? 'bg-green-500' : 'bg-red-500'}`;
                    }
                })
                .catch(() => {
                    const redisStatus = document.getElementById('redis-status');
                    if (redisStatus) {
                        redisStatus.className = 'w-6 h-6 rounded-full bg-red-500';
                    }
                });

            // Status do Database
            fetch('/admin/routines/database-status')
                .then(response => response.json())
                .then(data => {
                    const dbStatus = document.getElementById('database-status');
                    if (dbStatus) {
                        dbStatus.className = `w-6 h-6 rounded-full ${data.healthy ? 'bg-green-500' : 'bg-red-500'}`;
                    }
                })
                .catch(() => {
                    const dbStatus = document.getElementById('database-status');
                    if (dbStatus) {
                        dbStatus.className = 'w-6 h-6 rounded-full bg-red-500';
                    }
                });

            // Status Geral (baseado nos outros serviços)
            setTimeout(() => {
                const gptStatus = document.getElementById('gpt-status');
                const redisStatus = document.getElementById('redis-status');
                const dbStatus = document.getElementById('database-status');
                const overallStatus = document.getElementById('overall-status');
                
                if (gptStatus && redisStatus && dbStatus && overallStatus) {
                    const gptHealthy = gptStatus.classList.contains('bg-green-500');
                    const redisHealthy = redisStatus.classList.contains('bg-green-500');
                    const dbHealthy = dbStatus.classList.contains('bg-green-500');
                    
                    const allHealthy = gptHealthy && redisHealthy && dbHealthy;
                    const someHealthy = gptHealthy || redisHealthy || dbHealthy;
                    
                    if (allHealthy) {
                        overallStatus.className = 'w-6 h-6 rounded-full bg-green-500';
                    } else if (someHealthy) {
                        overallStatus.className = 'w-6 h-6 rounded-full bg-yellow-500';
                    } else {
                        overallStatus.className = 'w-6 h-6 rounded-full bg-red-500';
                    }
                }
            }, 1000);
        }

        // Função para mostrar resultados dos testes
        function showTestResults(testName, data) {
            const resultsContainer = document.getElementById('test-results');
            
            let resultHtml = `
                <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-yellow-400">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-black">${testName}</h4>
                        <span class="text-sm text-gray-500">${new Date().toLocaleTimeString()}</span>
                    </div>
            `;
            
            if (data.error) {
                resultHtml += `<div class="text-red-600">❌ ${data.error}</div>`;
            } else {
                resultHtml += `<div class="space-y-2">`;
                for (const [key, value] of Object.entries(data)) {
                    if (typeof value === 'boolean') {
                        resultHtml += `<div class="flex items-center">
                            <span class="w-4 h-4 rounded-full mr-2 ${value ? 'bg-green-500' : 'bg-red-500'}"></span>
                            <span class="text-sm text-gray-700">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}: ${value ? 'Sucesso' : 'Falha'}</span>
                        </div>`;
                    } else if (typeof value === 'number') {
                        resultHtml += `<div class="text-sm text-gray-700"><strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${value}</div>`;
                    } else if (typeof value === 'string') {
                        resultHtml += `<div class="text-sm text-gray-700"><strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${value}</div>`;
                    }
                }
                resultHtml += `</div>`;
            }
            
            resultHtml += `</div>`;
            
            // Adicionar ao início dos resultados
            resultsContainer.innerHTML = resultHtml + resultsContainer.innerHTML;
        }

        // Teste GPT
        document.getElementById('test-gpt-btn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<svg class="w-4 h-4 mr-1.5 animate-spin inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Executando...';
            
            fetch('/admin/routines/test-gpt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Atualizar indicadores
                updateIndicator('gpt-health-indicator', data.tests.health_check ? 'success' : 'error');
                updateIndicator('gpt-api-indicator', data.tests.api_test ? 'success' : 'error');
                updateIndicator('gpt-performance-indicator', data.tests.performance_test ? 'success' : 'error');
                
                // Mostrar resultados
                showTestResults('GPT', data);
            })
            .catch(error => {
                console.error('Erro no teste GPT:', error);
                showTestResults('GPT', { error: 'Erro na execução do teste' });
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>Executar Teste GPT';
            });
        });



        // Teste Cache Inteligente
        document.getElementById('test-cache-btn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<svg class="w-4 h-4 mr-1.5 animate-spin inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Executando...';
            
            fetch('/admin/routines/test-intelligent-cache', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Atualizar indicadores
                updateIndicator('cache-context-indicator', data.context_setup ? 'success' : 'error');
                updateIndicator('cache-response1-indicator', data.first_response ? 'success' : 'error');
                updateIndicator('cache-response2-indicator', data.second_response ? 'success' : 'error');
                
                // Mostrar resultados
                showTestResults('Cache Inteligente', data);
            })
            .catch(error => {
                console.error('Erro no teste Cache:', error);
                showTestResults('Cache Inteligente', { error: 'Erro na execução do teste' });
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>Executar Teste Cache';
            });
        });

        // Função para atualizar status do sistema
        function refreshSystemStatus() {
            fetch('{{ route("admin.routines.system-status") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar indicadores de status
                    updateIndicator('gpt-status', data.services.gpt.healthy ? 'success' : 'error');
                    updateIndicator('redis-status', data.services.redis.healthy ? 'success' : 'error');
                    updateIndicator('database-status', data.services.database.healthy ? 'success' : 'error');
                    updateIndicator('overall-status', data.overall_health ? 'success' : 'error');
                } else {
                    // Erro - marcar todos como erro
                    updateIndicator('gpt-status', 'error');
                    updateIndicator('redis-status', 'error');
                    updateIndicator('database-status', 'error');
                    updateIndicator('overall-status', 'error');
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar status:', error);
                // Erro - marcar todos como erro
                updateIndicator('gpt-status', 'error');
                updateIndicator('redis-status', 'error');
                updateIndicator('database-status', 'error');
                updateIndicator('overall-status', 'error');
            });
        }

        // Atualizar status ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            refreshSystemStatus();
        });

        // Atualizar status ao clicar no botão
        document.getElementById('refresh-status').addEventListener('click', refreshSystemStatus);
    </script>
@endsection
