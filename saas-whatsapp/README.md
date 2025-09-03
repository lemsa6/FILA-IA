# Sistema SaaS de Atendimento WhatsApp com IA

Este sistema consiste em uma plataforma SaaS multi-tenant para atendimento via WhatsApp com integração à API oficial da Meta, suporte a IA para respostas automatizadas, e painel administrativo completo com diferentes níveis de usuário.

## Arquitetura

O sistema utiliza:
- **Backend**: Laravel com multitenancy via `stancl/tenancy`
- **Frontend**: SPA em Vue.js com Inertia.js
- **Comunicação em Tempo Real**: Laravel WebSockets
- **Banco de Dados**: MySQL com bancos separados por tenant
- **Cache e Filas**: Redis compartilhado com prefixos por tenant
- **IA**: Integração com FILA-IA (Llama3 8B)

## Containers Docker

- **saas-api**: Container Laravel com a aplicação principal
- **saas-nginx**: Container Nginx como servidor web
- **saas-db**: Container MySQL para persistência
- **saas-websockets**: Container para o servidor WebSockets
- **fila-redis**: Container Redis existente do projeto FILA-IA (reutilizado)

## Implementação Modular

O sistema será implementado em módulos:

### Fase 1: Core do Sistema e WhatsApp
- Multitenancy e Painel Administrativo
- Sistema de Autenticação
- Configuração de WhatsApp
- Integração WhatsApp e Atendimento Humano

### Fase 2: Integração com IA
- Conexão com FILA-IA
- Automação de Atendimento
- Personalização por Tenant

### Fase 3: Recursos Avançados
- Relatórios e Análises Avançadas
- Integrações com Sistemas Externos
- Recursos Avançados de Segurança e Conformidade

## Configuração Inicial

1. Clone este repositório
2. Copie o arquivo `env.example` para `.env` e configure as variáveis
3. Execute `docker-compose up -d` para iniciar os containers
4. Acesse o sistema em http://localhost:8080

## Integração com FILA-IA

Este sistema utiliza a infraestrutura Redis existente do projeto FILA-IA para processamento de filas e cache. A comunicação com a IA é feita através da API do FILA-IA, utilizando chaves de API para autenticação. 