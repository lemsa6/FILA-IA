<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory, HasUuids;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'status',
        'rate_limit_minute',
        'rate_limit_hour',
        'rate_limit_day',
        'expires_at',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relacionamento com as requisições.
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    // Relacionamentos com planos e billing removidos - Sistema simplificado v2.4.0

    /**
     * Relacionamento com logs de uso de tokens.
     */
    public function tokenUsageLogs()
    {
        return $this->hasMany(TokenUsageLog::class);
    }

    // Métodos de planos removidos - Sistema simplificado v2.4.0
}
