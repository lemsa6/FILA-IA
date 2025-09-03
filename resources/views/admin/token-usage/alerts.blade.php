@extends('layouts.app')

@section('title', 'Alertas de Uso de Tokens')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Cabeçalho da Página -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">🚨 Alertas de Uso</h1>
                        <p class="text-gray-600 mt-1">Sistema simplificado - Monitoramento de tokens sem limites de custo</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            ✅ Sistema Ativo
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas de Alertas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $totalAlerts ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total de Alertas</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $criticalAlerts ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Críticos</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $warningAlerts ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Avisos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sistema Simplificado - Informações -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-900">Sistema Simplificado v2.4.0</h3>
                    <p class="text-blue-700 mt-1">
                        O sistema foi simplificado e não possui mais limites de custo ou planos complexos.
                        O monitoramento agora foca apenas no <strong>controle de tokens de entrada e saída</strong>.
                    </p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center text-sm text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Monitoramento de tokens em tempo real
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Sistema anti-flood inteligente ativo
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Performance 6x superior
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Cache GPT inteligente
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas Atuais -->
        @if(isset($alerts) && count($alerts) > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Alertas Ativos</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($alerts as $alert)
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                @if($alert['alert_level'] === 'critical')
                                    <div class="w-3 h-3 bg-red-400 rounded-full mt-2"></div>
                                @else
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full mt-2"></div>
                                @endif
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $alert['message'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">API Key: {{ $alert['api_key_name'] }}</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $alert['alert_level'] === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $alert['alert_level'] === 'critical' ? 'Crítico' : 'Aviso' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum Alerta Ativo</h3>
                <p class="mt-2 text-gray-500">
                    Sistema funcionando normalmente. Todos os indicadores estão dentro dos parâmetros esperados.
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.token-usage.stats') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Ver Estatísticas Detalhadas
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
