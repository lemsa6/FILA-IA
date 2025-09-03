<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TokenUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key_id',
        'plan_id',
        'request_id',
        'session_id',
        'prompt',
        'response',
        'tokens_input',
        'tokens_output',
        'total_tokens',
        'model_used',
        'cost_usd',
        'cost_brl',
        'usage_date',
        'usage_period',
        'status',
        'notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'total_tokens' => 'integer',
        'cost_usd' => 'decimal:6',
        'cost_brl' => 'decimal:2',
    ];

    // Relacionamentos
    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    // Scopes
    public function scopeByApiKey($query, $apiKeyId)
    {
        return $query->where('api_key_id', $apiKeyId);
    }

    public function scopeByPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('usage_date', $date);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('usage_period', $period);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Métodos auxiliares
    public function getFormattedCostUsdAttribute(): string
    {
        return '$' . number_format($this->cost_usd, 6);
    }

    public function getFormattedCostBrlAttribute(): string
    {
        return 'R$ ' . number_format($this->cost_brl, 2, ',', '.');
    }

    public function getTokensFormattedAttribute(): string
    {
        return number_format($this->tokens_input) . ' / ' . number_format($this->tokens_output);
    }
}
