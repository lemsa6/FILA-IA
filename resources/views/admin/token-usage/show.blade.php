@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detalhes da Requisição</h1>
                <p class="text-gray-600 mt-1">ID: {{ $request->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.token-usage.edit', $request->id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ✏️ Editar
                </a>
                <a href="{{ route('admin.token-usage.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Voltar
                </a>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            @php
                $statusColors = [
                    'completed' => 'bg-green-100 text-green-800',
                    'processing' => 'bg-blue-100 text-blue-800',
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'failed' => 'bg-red-100 text-red-800'
                ];
                $statusColor = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                @if($request->status === 'completed') ✅
                @elseif($request->status === 'processing') 🔄
                @elseif($request->status === 'pending') ⏳
                @elseif($request->status === 'failed') ❌
                @endif
                {{ ucfirst($request->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informações Básicas -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📋 Informações Básicas</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">API Key</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->apiKey->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($request->status) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prioridade</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->priority ?? 0 }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tentativas</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->attempts ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métricas de Performance -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">⚡ Performance</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tempo de Processamento</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $request->processing_time ? number_format($request->processing_time) . 'ms' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tempo de Resposta</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $request->response_time ? number_format($request->response_time) . 'ms' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Modelo</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->model ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tokens e Custos -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🪙 Tokens e Custos</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tokens de Entrada</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($request->tokens_input ?? 0) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tokens de Saída</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($request->tokens_output ?? 0) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Custo USD</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($request->cost_usd ?? 0, 6) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Custo BRL</label>
                            <p class="mt-1 text-sm text-gray-900">R$ {{ number_format($request->cost_brl ?? 0, 4) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🕒 Timestamps</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Criado em</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->created_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Iniciado em</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->started_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Completado em</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->completed_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Atualizado em</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $request->updated_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo e Resultado -->
        @if($request->content || $request->result)
        <div class="mt-6 space-y-6">
            @if($request->content)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📝 Conteúdo da Requisição</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ $request->content }}</pre>
                    </div>
                </div>
            </div>
            @endif

            @if($request->result)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">✅ Resultado</h2>
                    <div class="bg-green-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ $request->result }}</pre>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Erro (se houver) -->
        @if($request->error || $request->error_message)
        <div class="mt-6">
            <div class="bg-white rounded-xl border border-red-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-red-900 mb-4">❌ Erro</h2>
                    <div class="bg-red-50 rounded-lg p-4">
                        @if($request->error_message)
                            <p class="text-sm text-red-800 font-medium mb-2">Mensagem:</p>
                            <p class="text-sm text-red-700 mb-4">{{ $request->error_message }}</p>
                        @endif
                        @if($request->error)
                            <p class="text-sm text-red-800 font-medium mb-2">Detalhes:</p>
                            <pre class="text-sm text-red-700 whitespace-pre-wrap">{{ $request->error }}</pre>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Ações -->
        <div class="mt-8 flex justify-between">
            <form action="{{ route('admin.token-usage.destroy', $request->id) }}" method="POST" 
                  onsubmit="return confirm('Tem certeza que deseja excluir esta requisição?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                    🗑️ Excluir
                </button>
            </form>
            
            <div class="flex space-x-3">
                <a href="{{ route('admin.token-usage.edit', $request->id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ✏️ Editar
                </a>
                <a href="{{ route('admin.token-usage.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Voltar para Lista
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


