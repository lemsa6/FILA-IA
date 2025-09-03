@extends('layouts.app')

@section('title', 'Editar Chave de API')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.apikeys.update', $apiKey->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nome -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $apiKey->name) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrição -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $apiKey->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="active" {{ old('status', $apiKey->status) === 'active' ? 'selected' : '' }}>Ativa</option>
                                <option value="inactive" {{ old('status', $apiKey->status) === 'inactive' ? 'selected' : '' }}>Inativa</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Limite de requisições por minuto -->
                        <div class="mb-4">
                            <label for="rate_limit_minute" class="block text-sm font-medium text-gray-700">Limite de requisições por minuto</label>
                            <input type="number" name="rate_limit_minute" id="rate_limit_minute" value="{{ old('rate_limit_minute', $apiKey->rate_limit_minute) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('rate_limit_minute')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Limite de requisições por hora -->
                        <div class="mb-4">
                            <label for="rate_limit_hour" class="block text-sm font-medium text-gray-700">Limite de requisições por hora</label>
                            <input type="number" name="rate_limit_hour" id="rate_limit_hour" value="{{ old('rate_limit_hour', $apiKey->rate_limit_hour) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('rate_limit_hour')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Limite de requisições por dia -->
                        <div class="mb-4">
                            <label for="rate_limit_day" class="block text-sm font-medium text-gray-700">Limite de requisições por dia</label>
                            <input type="number" name="rate_limit_day" id="rate_limit_day" value="{{ old('rate_limit_day', $apiKey->rate_limit_day) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('rate_limit_day')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data de expiração -->
                        <div class="mb-4">
                            <label for="expires_at" class="block text-sm font-medium text-gray-700">Data de expiração (opcional)</label>
                            <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d') : '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('expires_at')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <a href="{{ route('admin.apikeys.index') }}" class="text-gray-600 hover:text-gray-900">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Atualizar Chave
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 