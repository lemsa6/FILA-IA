# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

## [1.0.0] - 2025-10-03 🎉 PRIMEIRA VERSÃO ESTÁVEL

### 🚀 Adicionado
- Sistema de logs de auditoria para tentativas de acesso à API
- Rate limiting configurado (30 req/min por IP)
- Validação aprimorada de formato de API keys
- Headers de segurança adicionais
- Configuração de produção (.env.production)
- Arquivo de versão (VERSION)

### 🔧 Corrigido
- Erro 404 em rotas da API (problema com brotli no nginx)
- Erro 405 em métodos POST (problema com location = /index.php)
- Erro array_merge com Collections do Eloquent
- Erro de tipo temperature (string → float)
- Parâmetros de requisição normalizados no modelo Request

### ⚡ Melhorado
- Performance das filas (timeout reduzido 25s → 20s)
- Workers do Horizon aumentados (8 → 12)
- Operações de banco consolidadas (múltiplas → única)
- Contexto de conversação otimizado (10 → 5 interações)
- Cache de autenticação implementado
- Logs condicionais (apenas > 3s ou cache miss)

### 🔒 Segurança
- Middleware de autenticação aprimorado
- Rate limiting por IP implementado
- Logs de auditoria para tentativas suspeitas
- Headers de segurança configurados
- Validação de entrada mais rigorosa

### 🐛 Removido
- Módulo brotli (causava conflitos)
- Script de otimização de assets (causava problemas)
- HTTP/2 Server Push (conflitava com POST)

## [0.1.0] - 2025-09-XX
### 🚀 Versão inicial
- API de processamento GPT
- Sistema de filas com Laravel Horizon
- Autenticação por API key
- Cache inteligente com Redis
- Circuit breaker para resiliência
