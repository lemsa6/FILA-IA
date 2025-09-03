<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOllamaRequest;
use App\Models\Request as OllamaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Paginação e filtros
        $perPage = min($request->input('per_page', 15), 100);
        $status = $request->input('status');
        
        $query = OllamaRequest::where('api_key_id', $request->apiKey->id)
            ->orderBy('created_at', 'desc');
            
        if ($status) {
            $query->where('status', $status);
        }
        
        $ollamaRequests = $query->paginate($perPage);
        
        return response()->json($ollamaRequests);
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
        ]);

        // Cria a requisição
        $ollamaRequest = new OllamaRequest();
        $ollamaRequest->id = (string) Str::uuid();
        $ollamaRequest->api_key_id = $request->apiKey->id;
        $ollamaRequest->content = json_encode($validated);
        $ollamaRequest->status = 'pending';
        $ollamaRequest->priority = $request->input('priority', 0);
        $ollamaRequest->save();

        // Dispara o job para processar a requisição
        ProcessOllamaRequest::dispatch($ollamaRequest);

        return response()->json([
            'id' => $ollamaRequest->id,
            'status' => $ollamaRequest->status,
            'created_at' => $ollamaRequest->created_at,
        ], 202);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Busca a requisição
        $ollamaRequest = OllamaRequest::where('id', $id)
            ->where('api_key_id', $request->apiKey->id)
            ->first();

        if (!$ollamaRequest) {
            return response()->json([
                'error' => 'Requisição não encontrada',
            ], 404);
        }

        // Monta a resposta
        $response = [
            'id' => $ollamaRequest->id,
            'status' => $ollamaRequest->status,
            'created_at' => $ollamaRequest->created_at,
        ];

        // Adiciona informações adicionais dependendo do status
        if ($ollamaRequest->status === 'completed') {
            $response['result'] = json_decode($ollamaRequest->result);
            $response['processing_time'] = $ollamaRequest->processing_time;
            $response['completed_at'] = $ollamaRequest->completed_at;
        } elseif ($ollamaRequest->status === 'failed') {
            $response['error'] = $ollamaRequest->error;
            $response['attempts'] = $ollamaRequest->attempts;
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
}
