<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OllamaRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'api_key_id',
        'session_id',
        'prompt',
        'response',
        'model',
        'parameters',
        'status',
        'response_time',
        'error_message',
        'metadata'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'parameters' => 'array',
        'metadata' => 'array',
        'response_time' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relacionamento com a chave de API
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * Escopo para requisições bem-sucedidas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Escopo para requisições com erro
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Escopo para requisições de hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Escopo para requisições das últimas 24 horas
     */
    public function scopeLast24Hours($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    /**
     * Calcula o tempo de resposta em milissegundos
     */
    public function calculateResponseTime()
    {
        if ($this->created_at && $this->updated_at) {
            $this->response_time = $this->created_at->diffInMilliseconds($this->updated_at);
            $this->save();
        }
    }

    /**
     * Marca a requisição como concluída
     */
    public function markAsCompleted($response, $model = null)
    {
        $this->update([
            'status' => 'completed',
            'response' => $response,
            'model' => $model,
            'response_time' => $this->created_at ? $this->created_at->diffInMilliseconds(now()) : null
        ]);
    }

    /**
     * Marca a requisição como falhada
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_time' => $this->created_at ? $this->created_at->diffInMilliseconds(now()) : null
        ]);
    }
}
