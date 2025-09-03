<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\FastProcessGPTRequest;
use App\Models\Request as GPTRequest;
use App\Services\IntelligentAIService;
use App\Services\TokenStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    /**
     * Serviço de IA inteligente
     *
     * @var IntelligentAIService
     */
    protected $intelligentAIService;

    /**
     * Construtor
     */
    public function __construct(IntelligentAIService $intelligentAIService)
    {
        $this->intelligentAIService = $intelligentAIService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Paginação e filtros
        $perPage = min($request->input('per_page', 15), 100);
        $status = $request->input('status');
        
        $query = GPTRequest::where('api_key_id', $request->apiKey->id)
            ->orderBy('created_at', 'desc');
            
        if ($status) {
            $query->where('status', $status);
        }
        
        $gptRequests = $query->paginate($perPage);
        
        return response()->json($gptRequests);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'prompt' => 'required|string|max:10000',
            'parameters' => 'sometimes|array',
            'metadata' => 'sometimes|array',
            'session_id' => 'sometimes|string|max:255', // ID da sessão para contexto
        ]);

        // Cria a requisição
        $gptRequest = new GPTRequest();
        $gptRequest->id = (string) Str::uuid();
        $gptRequest->api_key_id = $request->apiKey->id;
        $gptRequest->session_id = $validated['session_id'] ?? null;
        $gptRequest->content = json_encode($validated);
        $gptRequest->parameters = $validated['parameters'] ?? null;
        $gptRequest->metadata = $validated['metadata'] ?? null;
        $gptRequest->status = 'pending';
        $gptRequest->priority = $request->input('priority', 0);
        $gptRequest->attempts = 0;
        $gptRequest->ip_address = $request->ip();
        $gptRequest->user_agent = $request->userAgent();
        $gptRequest->save();

        // Dispara o job para processar a requisição GPT
        FastProcessGPTRequest::dispatch($gptRequest);

        return response()->json([
            'id' => $gptRequest->id,
            'status' => $gptRequest->status,
            'created_at' => $gptRequest->created_at,
            'session_id' => $validated['session_id'] ?? null,
            'message' => 'Requisição GPT enviada para processamento. Use o ID para consultar o status.',
        ], 202);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Busca a requisição
        $gptRequest = GPTRequest::where('id', $id)
            ->where('api_key_id', $request->apiKey->id)
            ->first();

        if (!$gptRequest) {
            return response()->json([
                'error' => 'Requisição não encontrada',
            ], 404);
        }

        // Monta a resposta
        $response = [
            'id' => $gptRequest->id,
            'status' => $gptRequest->status,
            'created_at' => $gptRequest->created_at,
        ];

        // Adiciona informações adicionais dependendo do status
        if ($gptRequest->status === 'completed') {
            $response['result'] = json_decode($gptRequest->result);
            $response['processing_time'] = $gptRequest->processing_time;
            $response['completed_at'] = $gptRequest->completed_at;
        } elseif ($gptRequest->status === 'failed') {
            $response['error'] = $gptRequest->error;
            $response['attempts'] = $gptRequest->attempts;
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Define o contexto base do produto/serviço
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setBaseContext(Request $request)
    {
        $validated = $request->validate([
            'base_context' => 'required|string|max:50000', // 50KB para contexto base
            'metadata' => 'sometimes|array',
        ]);

        $success = $this->intelligentAIService->setBaseContext(
            $request->apiKey->id,
            $validated['base_context'],
            $validated['metadata'] ?? []
        );

        if ($success) {
            return response()->json([
                'message' => 'Contexto base definido com sucesso',
                'context_info' => $this->intelligentAIService->getBaseContextInfo($request->apiKey->id)
            ], 200);
        }

        return response()->json([
            'error' => 'Erro ao definir contexto base'
        ], 500);
    }

    /**
     * Obtém o contexto base do produto/serviço
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBaseContext(Request $request)
    {
        $contextInfo = $this->intelligentAIService->getBaseContextInfo($request->apiKey->id);

        if (!$contextInfo) {
            return response()->json([
                'message' => 'Nenhum contexto base definido',
                'has_context' => false
            ], 200);
        }

        return response()->json([
            'has_context' => true,
            'context_info' => $contextInfo
        ], 200);
    }

    /**
     * Atualiza o contexto base do produto/serviço
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBaseContext(Request $request)
    {
        $validated = $request->validate([
            'base_context' => 'required|string|max:50000',
            'metadata' => 'sometimes|array',
        ]);

        $success = $this->intelligentAIService->updateBaseContext(
            $request->apiKey->id,
            $validated['base_context'],
            $validated['metadata'] ?? []
        );

        if ($success) {
            return response()->json([
                'message' => 'Contexto base atualizado com sucesso',
                'context_info' => $this->intelligentAIService->getBaseContextInfo($request->apiKey->id)
            ], 200);
        }

        return response()->json([
            'error' => 'Erro ao atualizar contexto base'
        ], 500);
    }

    /**
     * Remove o contexto base do produto/serviço
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBaseContext(Request $request)
    {
        $success = $this->intelligentAIService->removeBaseContext($request->apiKey->id);

        if ($success) {
            return response()->json([
                'message' => 'Contexto base removido com sucesso'
            ], 200);
        }

        return response()->json([
            'error' => 'Erro ao remover contexto base'
        ], 500);
    }

    /**
     * Obtém estatísticas de cache e uso
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCacheStats(Request $request)
    {
        $stats = $this->intelligentAIService->getCacheStats($request->apiKey->id);

        return response()->json([
            'cache_stats' => $stats,
            'message' => 'Estatísticas obtidas com sucesso'
        ], 200);
    }

    /**
     * Estatísticas rápidas de tokens (sistema ultra-otimizado)
     */
    public function fastStats(Request $request)
    {
        try {
            $apiKeyId = $request->apiKey->id; // Usar o API key do middleware
            $tokenStats = new TokenStatsService();
            
            $stats = $tokenStats->getStats($apiKeyId);
            
            return response()->json([
                'success' => true,
                'api_key_id' => $apiKeyId,
                'stats' => $stats,
                'generated_at' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao obter estatísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
