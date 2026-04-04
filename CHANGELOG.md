# Changelog

## [1.5.0] - 2026-04-04

### Added — Tool Calling Nativo (Function Calling OpenAI)

- ✅ `POST /api/v1/requests` aceita agora dois modos de operação:
  - **Modo simples** (retrocompatível): `{ prompt, session_id, ... }`
  - **Modo avançado** (novo): `{ messages[], tools[], parameters }`
- ✅ `IAService::generateCompletion()` ganhou parâmetros `messages` e `tools`
  - Quando `messages` é fornecido, usa diretamente sem construir do `prompt` + histórico
  - Quando `tools` é fornecido, inclui `tools` e `tool_choice: "auto"` no payload OpenAI
- ✅ `IAService::buildOpenAIParams()` aceita `tools[]` como terceiro parâmetro
- ✅ `IAService::convertOpenAIResponse()` detecta `finish_reason: "tool_calls"` e retorna:
  - `tool_calls[]` — lista de funções que a IA solicitou
  - `assistant_message` — mensagem completa do assistente para reinjetar no próximo turno
  - `done: false` — sinaliza que o ciclo ainda não terminou
- ✅ `FastProcessGPTRequest::handle()` extrai `messages` e `tools` do content JSON e repassa para `generateCompletion()`
- ✅ Validação de resultado aceita `tool_calls` além de `response` como resposta válida
- ✅ Cache de prompt desabilitado para requisições com tools (resultados dinâmicos)

### Changed

- `RequestController::store()` — `prompt` deixou de ser `required` para `sometimes`; adicionada validação que exige `prompt` OU `messages`
- `IAService` — timeout da chamada HTTP à OpenAI aumentado de `12s` para `30s` para acomodar modelos mais lentos em chamadas com tools

### Technical

- O loop de tool calling (execução das funções + reenvio dos resultados) é responsabilidade do **cliente** (ex.: WP-COMPLETO) — a FILA-IA funciona como pass-through para cada round individual, mantendo logs e custos por cliente
- Não há quebra de compatibilidade: clientes que enviam apenas `prompt` continuam funcionando sem alteração

---

## [1.4.0] - 2025-10-07

### Fixed
- ✅ Adicionado método `show()` faltante no TokenUsageController
- ✅ Adicionado método `edit()` faltante no TokenUsageController  
- ✅ Adicionado método `update()` faltante no TokenUsageController
- ✅ Adicionado método `destroy()` faltante no TokenUsageController
- ✅ Adicionado método `create()` faltante no TokenUsageController
- ✅ Adicionado método `store()` faltante no TokenUsageController
- ✅ Corrigido erro "Call to undefined method" em rotas REST

### Removed
- 🗑️ Removidos arquivos de configuração do Caddy do projeto
- 🗑️ Arquivos removidos: caddy-production.conf, caddy-minimal.conf, caddy-production-no-logs.conf

### Technical
- 🔧 Completado suporte completo para Route::resource no TokenUsageController
- 🔧 Melhorada compatibilidade com rotas REST padrão do Laravel
- 🔧 Projeto limpo sem arquivos de configuração de servidor

### API Status
- ✅ POST /api/v1/requests - Funcionando (Status 202)
- ✅ GET /api/v1/requests - Funcionando
- ✅ GET /api/v1/requests/{id} - Funcionando
- ✅ Autenticação por API Key - Funcionando
- ✅ CORS - Configurado corretamente

### Production Ready
- 🚀 Sistema pronto para produção
- 🚀 Todas as rotas administrativas funcionando
- 🚀 API endpoints testados e validados