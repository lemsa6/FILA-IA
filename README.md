# FILA-IA

Intermediário inteligente entre sistemas Laravel e a API OpenAI. Gerencia filas de requisições, autenticação por chave de API, cache de contexto por cliente, tracking de tokens/custos e suporte a **tool calling nativo** (function calling da OpenAI).

## Versão 1.5.0

### Funcionalidades

- **Fila assíncrona**: Requisições processadas via Laravel Horizon + Redis
- **Isolamento por cliente**: Contexto, cache e billing separados por `api_key_id`
- **Tool calling nativo**: Suporte completo a `tools[]` e `messages[]` no formato OpenAI
- **Tracking de custos**: Cálculo automático USD/BRL por token (input + output)
- **Cache de prompt**: Respostas idênticas reutilizadas (desativado para tool calls)
- **Circuit breaker**: Fallback automático em caso de indisponibilidade da OpenAI
- **Estatísticas por cliente**: Tokens diários, mensais e totais via Redis

### Arquitetura

```
Cliente (ex: WP-COMPLETO)
    │
    ├── POST /api/v1/requests  { prompt } ou { messages, tools }
    │         ↓
    │   RequestController → FastProcessGPTRequest (job)
    │         ↓
    │   IAService → OpenAI API (gpt-4.1-nano)
    │         ↓
    │   resultado salvo no DB
    │
    └── GET /api/v1/requests/{id}  ← polling até status = completed
```

- **Backend**: Laravel 11 · PHP 8.2+
- **Filas**: Redis + Laravel Horizon
- **Banco**: MySQL 8.0+
- **Cache**: Redis

### Requisitos

- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- Conta OpenAI com API Key

### Instalação

```bash
git clone https://github.com/lemsa6/FILA-IA.git
cd FILA-IA

cp .env.example .env
# Preencha OPENAI_API_KEY, DB_*, REDIS_HOST no .env

composer install
php artisan key:generate
php artisan migrate

# Inicia o worker de filas
php artisan horizon
```

### Variáveis de ambiente principais

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4.1-nano

DB_HOST=127.0.0.1
DB_DATABASE=fila_ia

REDIS_HOST=127.0.0.1
```

### API — Endpoints principais

#### Autenticação

Todas as requisições exigem o header:
```
X-API-Key: sua-chave-de-api
```

#### POST /api/v1/requests — Enviar requisição

**Modo simples (prompt):**
```json
{
  "prompt": "Olá, como posso agendar uma consulta?",
  "session_id": "tenant_1_contact_42",
  "parameters": { "temperature": 0.7, "max_tokens": 500 }
}
```

**Modo avançado (tool calling):**
```json
{
  "messages": [
    { "role": "system", "content": "Você é um assistente de agendamentos." },
    { "role": "user",   "content": "Quais horários estão disponíveis?" }
  ],
  "tools": [
    {
      "type": "function",
      "function": {
        "name": "get_available_slots",
        "description": "Busca horários disponíveis no calendário.",
        "parameters": { "type": "object", "properties": {} }
      }
    }
  ],
  "parameters": { "temperature": 0.7, "max_tokens": 500 }
}
```

**Resposta (202):**
```json
{ "id": "uuid", "status": "pending", "message": "Use o ID para consultar o status." }
```

#### GET /api/v1/requests/{id} — Consultar resultado

**Concluído com texto:**
```json
{
  "status": "completed",
  "result": {
    "finish_reason": "stop",
    "response": "Resposta da IA aqui",
    "tokens_input": 120,
    "tokens_output": 45,
    "done": true
  }
}
```

**Concluído com tool_calls:**
```json
{
  "status": "completed",
  "result": {
    "finish_reason": "tool_calls",
    "tool_calls": [{ "id": "call_abc", "function": { "name": "get_available_slots", "arguments": "{}" } }],
    "assistant_message": { "role": "assistant", "tool_calls": [...] },
    "done": false
  }
}
```

> Quando `finish_reason = "tool_calls"`: execute as funções localmente, adicione os resultados como `role: "tool"` no array `messages` e reenvie via POST. O loop continua até `finish_reason = "stop"`.

Documentação completa da API: [`docs/guia-api-cliente.md`](docs/guia-api-cliente.md)

### Monitoramento

- **Horizon Dashboard**: `http://localhost:8000/horizon`
- **Logs**: `storage/logs/laravel.log`
- **Stats de tokens por cliente**: `GET /api/v1/stats/fast`

### Performance

- Tempo médio por requisição: **1–3 segundos**
- Cache hit evita chamada à OpenAI para prompts idênticos
- Tracking de tokens apenas em Redis (zero queries extras por requisição)

---

**Desenvolvido por lemsa6** | [GitHub](https://github.com/lemsa6/FILA-IA)
