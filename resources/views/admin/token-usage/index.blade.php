@extends('layouts.app')

@section('title', 'Logs de Uso de Tokens')

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
                <h1 class="text-2xl font-bold text-gray-900">Logs de Uso de Tokens</h1>
                <p class="text-gray-600 mt-1">Histórico completo de consumo de tokens e custos</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.token-usage.stats') }}" class="btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Estatísticas
                </a>
                <a href="{{ route('admin.token-usage.alerts') }}" class="btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Alertas
                </a>
                <a href="{{ route('admin.token-usage.create') }}" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Novo Log
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.token-usage.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="api_key_id" class="block text-sm font-medium text-gray-700 mb-1">Chave de API</label>
                        <select name="api_key_id" id="api_key_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todas as chaves</option>
                            @foreach($apiKeys ?? [] as $apiKey)
                                <option value="{{ $apiKey->id }}" {{ request('api_key_id') == $apiKey->id ? 'selected' : '' }}>
                                    {{ $apiKey->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">📅 Data de Entrada</label>
                        <input type="date" name="start_date" id="start_date" 
                               value="{{ request('start_date', $stats['period_start'] ?? '') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">📅 Data de Saída</label>
                        <input type="date" name="end_date" id="end_date" 
                               value="{{ request('end_date', $stats['period_end'] ?? '') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            🔍 Filtrar
                        </button>
                    </div>
                </div>
                
                <!-- Período Atual -->
                <div class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                    📊 <strong>Período atual:</strong> {{ \Carbon\Carbon::parse($stats['period_start'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($stats['period_end'])->format('d/m/Y') }}
                    @if(!request('start_date') && !request('end_date'))
                        <span class="text-indigo-600 font-medium">(Últimos 30 dias - padrão)</span>
                    @endif
                </div>
            </form>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_requests'] ?? 0) }}</div>
                <div class="text-sm text-gray-600">📊 Requisições</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['total_input_tokens'] ?? 0) }}</div>
                <div class="text-sm text-gray-600">🔤 Tokens Entrada</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_output_tokens'] ?? 0) }}</div>
                <div class="text-sm text-gray-600">💬 Tokens Saída</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-red-600">${{ number_format($stats['total_cost_usd'] ?? 0, 6) }}</div>
                <div class="text-sm text-gray-600">💰 Total USD</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-orange-600">R$ {{ number_format($stats['total_cost_brl'] ?? 0, 4) }}</div>
                <div class="text-sm text-gray-600">🇧🇷 Total BRL</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-purple-600">{{ number_format(($stats['avg_processing_time'] ?? 0), 0) }}ms</div>
                <div class="text-sm text-gray-600">⚡ Tempo Médio</div>
            </div>
        </div>

        <!-- Tabela de Logs -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chave API</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-blue-600 font-bold text-sm">{{ substr($log->apiKey->name ?? 'N/A', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $log->apiKey->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $log->model_used ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <div class="text-gray-900">
                                            <span class="font-medium">Entrada:</span> {{ number_format($log->tokens_input) }}
                                        </div>
                                        <div class="text-gray-500">
                                            <span class="font-medium">Saída:</span> {{ number_format($log->tokens_output) }}
                                        </div>
                                        <div class="text-gray-700 font-medium">
                                            <span class="font-medium">Total:</span> {{ number_format($log->total_tokens) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <div class="text-gray-900 font-medium">R$ {{ number_format($log->cost_brl, 4, ',', '.') }}</div>
                                        <div class="text-gray-500">$ {{ number_format($log->cost_usd, 6) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($log->status === 'success')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Sucesso
                                        </span>
                                    @elseif ($log->status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Falha
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                            </svg>
                                            Parcial
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <div class="text-gray-900 font-medium">{{ $log->usage_date ? \Carbon\Carbon::parse($log->usage_date)->format('d/m/Y') : 'N/A' }}</div>
                                        <div class="text-gray-500">{{ $log->created_at ? $log->created_at->format('H:i') : 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.token-usage.show', $log->id) }}" 
                                           class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Ver
                                        </a>
                                        <a href="{{ route('admin.token-usage.edit', $log->id) }}" 
                                           class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Editar
                                        </a>
                                        <form action="{{ route('admin.token-usage.destroy', $log->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors duration-200"
                                                    onclick="return confirm('Tem certeza que deseja excluir este log?')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-600 mb-2">Nenhum log de uso encontrado</p>
                                        <p class="text-gray-500">Os logs aparecerão aqui conforme o uso da API</p>
                                        <a href="{{ route('admin.token-usage.create') }}" class="btn-secondary mt-4 inline-block">
                                            Criar Primeiro Log
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação -->
        @if($logs->hasPages())
            <div class="mt-6">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

