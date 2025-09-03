<?php

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory, HasUuids;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'api_key_id',
        'session_id',
        'content',
        'parameters',
        'metadata',
        'result',
        'status',
        'priority',
        'attempts',
        'error',
        'error_message',
        'model',
        'ip_address',
        'user_agent',
        'processing_time',
        'response_time',
        'tokens_input',
        'tokens_output',
        'completed_at',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parameters' => 'array',
        'metadata' => 'array',
        'processing_time' => 'float',
        'response_time' => 'float',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'priority' => 'integer',
        'attempts' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Acessor para manter compatibilidade com código existente
     */
    public function getPromptAttribute()
    {
        return $this->content;
    }

    /**
     * Mutator para manter compatibilidade com código existente
     */
    public function setPromptAttribute($value)
    {
        $this->attributes['content'] = $value;
    }

    /**
     * Acessor para manter compatibilidade com código existente
     */
    public function getResponseAttribute()
    {
        return $this->result;
    }

    /**
     * Mutator para manter compatibilidade com código existente
     */
    public function setResponseAttribute($value)
    {
        $this->attributes['result'] = $value;
    }

    /**
     * Acessor para descriptografar o conteúdo
     */
    public function getContentAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        $encryptionService = app(EncryptionService::class);
        return $encryptionService->decrypt($value);
    }

    /**
     * Mutator para criptografar o conteúdo
     */
    public function setContentAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['content'] = $value;
            return;
        }
        
        $encryptionService = app(EncryptionService::class);
        $this->attributes['content'] = $encryptionService->encrypt($value);
    }

    /**
     * Acessor para descriptografar o resultado
     */
    public function getResultAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        $encryptionService = app(EncryptionService::class);
        return $encryptionService->decrypt($value);
    }

    /**
     * Mutator para criptografar o resultado
     */
    public function setResultAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['result'] = $value;
            return;
        }
        
        $encryptionService = app(EncryptionService::class);
        $this->attributes['result'] = $encryptionService->encrypt($value);
    }

    /**
     * Relacionamento com a chave de API.
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * Escopo para requisições em processamento.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Escopo para requisições concluídas.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Escopo para requisições com erro.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
