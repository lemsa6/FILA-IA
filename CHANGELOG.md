# Changelog

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