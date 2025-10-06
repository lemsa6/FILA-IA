<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestAIController extends Controller
{
    /**
     * Exibe a tela de teste da IA
     */
    public function index()
    {
        return view('admin.test-ai');
    }

    /**
     * Executa o teste da IA
     */
    public function test(Request $request)
    {
        try {
            // Perguntas pré-definidas para teste
            $questions = [
                "Qual é a capital do Brasil?",
                "Explique o que é inteligência artificial em uma frase",
                "Conte uma piada curta",
                "Quais são os benefícios do Docker?",
                "Como funciona o Laravel?",
                "O que é machine learning?",
                "Explique o conceito de API REST",
                "Quais são as melhores práticas de segurança?",
                "Como otimizar um banco de dados?",
                "O que é DevOps?"
            ];

            // Seleciona pergunta aleatória
            $randomQuestion = $questions[array_rand($questions)];

            // Instancia o serviço GPT
            $iaService = app(IAService::class);

            // Verifica se o serviço está funcionando
            if (!$iaService->healthCheck()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Serviço OpenAI GPT não está respondendo'
                ], 503);
            }

            // Gera resposta da IA
            $response = $iaService->generateCompletion($randomQuestion, [
                'temperature' => 0.7,
                'max_tokens' => 200
            ]);

            // Log do teste
            Log::info('Teste de IA executado com sucesso', [
                'user_id' => auth()->id(),
                'question' => $randomQuestion,
                'model' => config('services.openai.model', 'gpt-4.1-nano')
            ]);

            return response()->json([
                'success' => true,
                'question' => $randomQuestion,
                'response' => $response['response'] ?? 'Resposta não disponível',
                'model' => 'gpt-4.1-nano',
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'cache_hit' => $response['_cache_info']['cache_hit'] ?? false,
                'tokens_input' => $response['tokens_input'] ?? 0,
                'tokens_output' => $response['tokens_output'] ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao executar teste de IA', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao executar teste: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica o status da IA
     */
    public function status()
    {
        try {
            $iaService = app(IAService::class);
            $isHealthy = $iaService->healthCheck();

            return response()->json([
                'healthy' => $isHealthy,
                'model' => 'OpenAI GPT-4.1-nano',
                'url' => config('services.openai.api_url', 'https://api.openai.com/v1'),
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da IA', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'healthy' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
