@extends('layouts.app')

@section('title', 'Teste de IA')

@section('content')

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Cabeçalho da Página -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">🤖 Teste da Inteligência Artificial</h1>
                <p class="text-gray-600">Teste se o OpenAI GPT-4.1-nano está funcionando corretamente</p>
            </div>

            <!-- Card de Status -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status da IA</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm font-medium text-gray-600 mb-1">Modelo</div>
                            <div class="text-gray-900 font-semibold">OpenAI GPT-4.1-nano</div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm font-medium text-gray-600 mb-1">API OpenAI</div>
                            <div class="text-gray-900 font-semibold">{{ config('services.openai.api_url', 'https://api.openai.com/v1') }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm font-medium text-gray-600 mb-1">Status</div>
                            <div id="ai-status" class="text-gray-900 font-semibold">Verificando...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão de Teste -->
            <div class="text-center mb-6">
                <button id="test-ai-btn" class="btn-primary px-8 py-3 text-lg">
                    🧪 Testar IA
                </button>
            </div>

            <!-- Resultado do Teste -->
            <div id="test-result" class="hidden">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Resultado do Teste</h3>
                        
                        <!-- Pergunta -->
                        <div class="mb-6">
                            <div class="text-sm font-medium text-gray-600 mb-2">Pergunta:</div>
                            <div id="question-display" class="bg-gray-50 p-4 rounded-lg border text-gray-800"></div>
                        </div>

                        <!-- Resposta -->
                        <div class="mb-6">
                            <div class="text-sm font-medium text-gray-600 mb-2">Resposta da IA:</div>
                            <div id="response-display" class="bg-gray-50 p-4 rounded-lg border text-gray-800 min-h-[100px]"></div>
                        </div>

                        <!-- Informações -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium">Modelo:</span>
                                <span id="model-display" class="ml-2"></span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium">Timestamp:</span>
                                <span id="timestamp-display" class="ml-2"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="loading" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <div class="mt-2 text-gray-600">Processando requisição...</div>
            </div>

            <!-- Erro -->
            <div id="error-display" class="hidden bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="text-red-700">
                    <div class="font-semibold">Erro:</div>
                    <div id="error-message"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testBtn = document.getElementById('test-ai-btn');
            const testResult = document.getElementById('test-result');
            const loading = document.getElementById('loading');
            const errorDisplay = document.getElementById('error-display');
            const aiStatus = document.getElementById('ai-status');

            // Verificar status da IA ao carregar a página
            checkAIStatus();

            testBtn.addEventListener('click', function() {
                executeTest();
            });

            function checkAIStatus() {
                fetch('/admin/test-ai/status')
                    .then(response => response.json())
                    .then(data => {
                        if (data.healthy) {
                            aiStatus.textContent = '✅ Conectado';
                            aiStatus.className = 'text-green-700';
                        } else {
                            aiStatus.textContent = '❌ Desconectado';
                            aiStatus.className = 'text-red-700';
                        }
                    })
                    .catch(() => {
                        aiStatus.textContent = '❌ Erro de conexão';
                        aiStatus.className = 'text-red-700';
                    });
            }

            function executeTest() {
                // Reset displays
                testResult.classList.add('hidden');
                errorDisplay.classList.add('hidden');
                loading.classList.remove('hidden');
                testBtn.disabled = true;

                fetch('/admin/test-ai/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('hidden');
                    testBtn.disabled = false;

                    if (data.success) {
                        // Exibir resultado
                        document.getElementById('question-display').textContent = data.question;
                        document.getElementById('response-display').textContent = data.response;
                        document.getElementById('model-display').textContent = data.model;
                        document.getElementById('timestamp-display').textContent = data.timestamp;
                        
                        testResult.classList.remove('hidden');
                    } else {
                        // Exibir erro
                        document.getElementById('error-message').textContent = data.error;
                        errorDisplay.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    loading.classList.add('hidden');
                    testBtn.disabled = false;
                    
                    document.getElementById('error-message').textContent = 'Erro de conexão: ' + error.message;
                    errorDisplay.classList.remove('hidden');
                });
            }
        });
    </script>
@endsection
