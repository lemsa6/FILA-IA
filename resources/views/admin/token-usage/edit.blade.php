@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Editar Requisição</h1>
                <p class="text-gray-600 mt-1">ID: {{ $request->id }}</p>
            </div>
            <a href="{{ route('admin.token-usage.show', $request->id) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                ← Voltar
            </a>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="p-6">
                <form action="{{ route('admin.token-usage.update', $request->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Status -->
                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status da Requisição
                        </label>
                        <select name="status" id="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>
                                ⏳ Pendente
                            </option>
                            <option value="processing" {{ $request->status === 'processing' ? 'selected' : '' }}>
                                🔄 Processando
                            </option>
                            <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>
                                ✅ Completada
                            </option>
                            <option value="failed" {{ $request->status === 'failed' ? 'selected' : '' }}>
                                ❌ Falhada
                            </option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prioridade -->
                    <div class="mb-6">
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Prioridade (0-10)
                        </label>
                        <input type="number" name="priority" id="priority" min="0" max="10" 
                               value="{{ old('priority', $request->priority ?? 0) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            0 = Baixa prioridade, 10 = Alta prioridade
                        </p>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Informações Somente Leitura -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">📋 Informações (Somente Leitura)</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">API Key:</span>
                                <span class="text-gray-900">{{ $request->apiKey->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Tentativas:</span>
                                <span class="text-gray-900">{{ $request->attempts ?? 0 }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Tokens Entrada:</span>
                                <span class="text-gray-900">{{ number_format($request->tokens_input ?? 0) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Tokens Saída:</span>
                                <span class="text-gray-900">{{ number_format($request->tokens_output ?? 0) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Custo USD:</span>
                                <span class="text-gray-900">${{ number_format($request->cost_usd ?? 0, 6) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Custo BRL:</span>
                                <span class="text-gray-900">R$ {{ number_format($request->cost_brl ?? 0, 4) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-between">
                        <a href="{{ route('admin.token-usage.show', $request->id) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            💾 Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aviso -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Atenção
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Apenas o status e a prioridade podem ser editados. 
                            Outras informações como tokens e custos são calculadas automaticamente pelo sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


