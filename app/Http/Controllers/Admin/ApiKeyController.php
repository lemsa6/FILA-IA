<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $apiKeys = ApiKey::orderBy('created_at', 'desc')->get();
        return view('admin.apikeys.index', compact('apiKeys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.apikeys.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'rate_limit_minute' => 'required|integer|min:1',
            'rate_limit_hour' => 'required|integer|min:1',
            'rate_limit_day' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Gera uma chave API aleatória
        $key = Str::random(64);

        // Cria o registro no banco de dados
        $apiKey = new ApiKey();
        $apiKey->key = $key;
        $apiKey->name = $validated['name'];
        $apiKey->description = $validated['description'];
        $apiKey->status = 'active';
        $apiKey->rate_limit_minute = $validated['rate_limit_minute'];
        $apiKey->rate_limit_hour = $validated['rate_limit_hour'];
        $apiKey->rate_limit_day = $validated['rate_limit_day'];
        
        if (!empty($validated['expires_at'])) {
            $apiKey->expires_at = $validated['expires_at'];
        }

        $apiKey->save();

        return redirect()->route('admin.apikeys.index')
            ->with('success', 'Chave de API criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $apiKey = ApiKey::findOrFail($id);
        return view('admin.apikeys.show', compact('apiKey'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $apiKey = ApiKey::findOrFail($id);
        return view('admin.apikeys.edit', compact('apiKey'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $apiKey = ApiKey::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'rate_limit_minute' => 'required|integer|min:1',
            'rate_limit_hour' => 'required|integer|min:1',
            'rate_limit_day' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $apiKey->name = $validated['name'];
        $apiKey->description = $validated['description'];
        $apiKey->status = $validated['status'];
        $apiKey->rate_limit_minute = $validated['rate_limit_minute'];
        $apiKey->rate_limit_hour = $validated['rate_limit_hour'];
        $apiKey->rate_limit_day = $validated['rate_limit_day'];
        
        if (!empty($validated['expires_at'])) {
            $apiKey->expires_at = $validated['expires_at'];
        } else {
            $apiKey->expires_at = null;
        }

        $apiKey->save();

        return redirect()->route('admin.apikeys.index')
            ->with('success', 'Chave de API atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return redirect()->route('admin.apikeys.index')
            ->with('success', 'Chave de API excluída com sucesso.');
    }
}
