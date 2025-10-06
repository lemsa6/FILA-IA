# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

## [1.2.0] - 2025-10-06 🧹 SISTEMA LIMPO E OTIMIZADO

### 🚀 Adicionado
- Sistema 100% funcional apenas com migrations
- Estrutura de banco limpa e otimizada
- Migrations organizadas e sem duplicatas

### 🔧 Melhorado
- Removidas 6 migrations desnecessárias (plans, billing_cycles, token_usage_logs)
- Eliminadas referências órfãs no código
- Sistema mais leve e performático
- Migrations testadas e funcionais do zero

### 🗑️ Removido
- Tabelas não utilizadas: plans, billing_cycles, plan_assignments, token_usage_logs
- Models desnecessários: TokenUsageLog
- Migrations duplicadas e vazias
- Referências órfãs no código

### 💡 Funcionalidades
- Sistema funciona 100% com migrate:fresh
- 11 migrations essenciais mantidas
- Todas as 27 rotas admin funcionando
- Zero dependências de dumps de banco

## [1.1.0] - 2025-10-03 💰 SISTEMA DE CUSTOS IMPLEMENTADO

### 🚀 Adicionado
- Sistema completo de cálculo de custos por token
- Campos cost_usd e cost_brl na tabela requests
- Cálculo automático de custos no FastProcessGPTRequest
- Exibição de custos reais no painel administrativo
- Preços atualizados GPT-4.1-nano (Out/2025)

### 🔧 Melhorado
- TokenUsageController agora exibe custos reais
- Estatísticas de uso incluem valores em USD e BRL
- Model Request com casting adequado para campos decimais
- Precisão de 6 casas decimais para USD e 4 para BRL

### 💡 Funcionalidades
- Cálculo baseado em preços reais: $0.20/1M tokens input, $0.80/1M tokens output
- Taxa de câmbio configurável (atual: R$ 5.50)
- Compatibilidade total com sistema existente
- Zero impacto em performance

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
