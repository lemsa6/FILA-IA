@extends('layouts.app')

@section('title', 'Detalhes da Chave de API')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">Informações da Chave</h3>

                        <div class="mb-4 p-4 bg-gray-100 rounded">
                            <div class="font-bold mb-2">Chave de API:</div>
                            <div class="break-all bg-gray-200 p-2 rounded">{{ $apiKey->key }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="font-bold">Nome:</div>
                                <div>{{ $apiKey->name }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Status:</div>
                                <div>
                                    @if ($apiKey->status === 'active')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Ativa
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inativa
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="font-bold">Criada em:</div>
                                <div>{{ $apiKey->created_at->format('d/m/Y H:i') }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Expira em:</div>
                                <div>{{ $apiKey->expires_at ? $apiKey->expires_at->format('d/m/Y') : 'Não expira' }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Último uso:</div>
                                <div>{{ $apiKey->last_used_at ? $apiKey->last_used_at->format('d/m/Y H:i') : 'Nunca utilizada' }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Descrição:</div>
                                <div>{{ $apiKey->description ?: 'Sem descrição' }}</div>
                            </div>
                        </div>

                        <h3 class="text-lg font-medium my-4">Limites de Requisições</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="font-bold">Por minuto:</div>
                                <div>{{ $apiKey->rate_limit_minute }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Por hora:</div>
                                <div>{{ $apiKey->rate_limit_hour }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Por dia:</div>
                                <div>{{ $apiKey->rate_limit_day }}</div>
                            </div>
                        </div>

                        <h3 class="text-lg font-medium my-4">Estatísticas de Uso</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="font-bold">Total de requisições:</div>
                                <div>{{ $apiKey->requests()->count() }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Requisições hoje:</div>
                                <div>{{ $apiKey->requests()->whereDate('created_at', now())->count() }}</div>
                            </div>

                            <div>
                                <div class="font-bold">Requisições na última hora:</div>
                                <div>{{ $apiKey->requests()->where('created_at', '>=', now()->subHour())->count() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <a href="{{ route('admin.apikeys.index') }}" class="text-gray-600 hover:text-gray-900">
                            Voltar para a lista
                        </a>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.apikeys.edit', $apiKey->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Editar
                            </a>
                            <form action="{{ route('admin.apikeys.destroy', $apiKey->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Tem certeza que deseja excluir esta chave?')">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 