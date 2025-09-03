# Documentação Técnica: Sistema SaaS de Atendimento WhatsApp (Versão Simplificada)

## Sumário
1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Arquitetura Técnica](#2-arquitetura-técnica)
3. [Sistema de Autenticação](#3-sistema-de-autenticação)
4. [Integração com WhatsApp](#4-integração-com-whatsapp)
5. [Comunicação em Tempo Real](#5-comunicação-em-tempo-real)
6. [Integração com API FILA-IA](#6-integração-com-api-fila-ia)
7. [Sistema de Backup](#7-sistema-de-backup)
8. [Segurança](#8-segurança)
9. [Considerações de Implementação](#9-considerações-de-implementação)
10. [Conclusão](#10-conclusão)

## 1. Visão Geral do Sistema

O sistema consiste em uma plataforma de atendimento via WhatsApp com integração à API oficial da Meta, onde cada cliente terá sua própria instância isolada em containers Docker. A arquitetura é projetada para simplicidade, facilidade de manutenção e conformidade com a LGPD.

### 1.1 Objetivos do Sistema

- Fornecer uma plataforma profissional de atendimento via WhatsApp
- Garantir isolamento completo de dados entre clientes (containers separados)
- Oferecer interface simples e funcional para atendentes
- Manter conformidade com LGPD e outras regulamentações
- Proporcionar comunicação em tempo real para melhor experiência de atendimento
- Possibilitar respostas automatizadas através da integração com a API FILA-IA

### 1.2 Principais Funcionalidades

- Integração oficial com WhatsApp Business API
- Dashboard administrativo com múltiplos níveis de acesso
- Sistema de atendimento com comunicação em tempo real
- Histórico de conversas e mensagens
- Gerenciamento de usuários e permissões
- Backup automatizado
- Integração com API FILA-IA para respostas automatizadas

## 2. Arquitetura Técnica

### 2.1 Componentes Principais

- **Backend**: Laravel padrão
- **Frontend**: Laravel Blade com componentes Vue.js para interatividade
- **Comunicação em Tempo Real**: Laravel WebSockets
- **Banco de Dados**: MySQL dedicado por cliente
- **Infraestrutura**: Docker com containers independentes por cliente
- **IA**: Integração com API FILA-IA (Llama3 8B via Ollama)

### 2.2 Diagrama de Arquitetura

```
graph TD
    subgraph "Arquitetura do Sistema (Por Cliente)"
        A[Cliente WhatsApp] --> B[API WhatsApp]
        B --> C[Sistema de Atendimento]
        C --> D[Banco de Dados]
        C --> E[WebSockets]
        E --> F[Frontend]
        C --> G[Sistema de Autenticação]
        C <--> H[API FILA-IA]
        C --> I[Sistema de Backup]
    end
```

### 2.3 Pacotes Principais

| Pacote | Função |
|--------|--------|
| `gorkalaucirica/laravel-whatsapp` | Integração oficial com WhatsApp API da Meta |
| `laravel/breeze` | Sistema de autenticação básico |
| `spatie/laravel-permission` | Controle de papéis (admin, gerente, atendente) |
| `beyondcode/laravel-websockets` | Servidor WebSocket para comunicação em tempo real |
| `predis/predis` | Cliente Redis para broadcasting de eventos |
| `spatie/laravel-backup` | Sistema de backup automatizado |
| `spatie/laravel-activitylog` | Registro de logs de ações dos usuários |
| `guzzlehttp/guzzle` | Cliente HTTP para comunicação com a API FILA-IA |

### 2.4 Infraestrutura

- **Containers Docker por Cliente**:
  - Container de Aplicação Laravel com Nginx
  - Container MySQL para banco de dados
  - Container Redis para WebSockets (opcional, pode usar o driver de banco de dados)

- **Requisitos de Hardware Recomendados (por cliente)**:
  - CPU: 2 núcleos
  - RAM: 2GB mínimo
  - Armazenamento: 20GB SSD
  - Rede: Conexão estável

### 2.5 Fluxo de Dados

1. Cliente envia mensagem via WhatsApp
2. API WhatsApp encaminha para o sistema via webhook
3. Sistema processa a mensagem e notifica atendentes via WebSockets
4. Sistema decide entre encaminhar para atendente humano ou API FILA-IA
5. Resposta (humana ou automatizada) enviada ao cliente via API WhatsApp
6. Dados armazenados no banco de dados
7. Interface atualizada em tempo real via WebSockets

## 3. Sistema de Autenticação

### 3.1 Métodos de Autenticação

- **Email/Senha**: Autenticação padrão com Laravel Breeze

### 3.2 Níveis de Acesso

- **Admin**: Acesso total ao sistema
- **Gerente**: Acesso a relatórios e configurações limitadas
- **Atendente**: Acesso apenas ao painel de atendimento

### 3.3 Fluxo de Autenticação

1. Usuário acessa o sistema
2. Usuário fornece credenciais (email/senha)
3. Sistema valida credenciais no banco de dados
4. Sessão é criada
5. Permissões são carregadas conforme o nível de acesso

### 3.4 Conformidade com LGPD

- **Consentimento**: Sistema de opt-in para coleta e processamento de dados
- **Registro de Atividades**: Log detalhado de todas as operações com dados pessoais
- **Direito ao Esquecimento**: Funcionalidade para anonimização/exclusão de dados
- **Criptografia**: Dados sensíveis criptografados em repouso e em trânsito

## 4. Integração com WhatsApp

### 4.1 Configuração

- API Key WhatsApp Business armazenada criptografada
- Configuração de webhook
- Templates de mensagem aprovados pela Meta
- Interface para configurar credenciais da API WhatsApp

### 4.2 Fluxo de Mensagens

1. Cliente envia mensagem via WhatsApp
2. Webhook da Meta envia para o sistema
3. Mensagem é processada e armazenada
4. Atendente é notificado em tempo real
5. Resposta enviada via API WhatsApp

### 4.3 Tipos de Mensagens Suportadas

- Texto simples
- Imagens, áudio e vídeo
- Documentos
- Botões interativos
- Templates aprovados pela Meta

### 4.4 Gerenciamento de Templates

- Interface para criação e submissão de templates
- Acompanhamento de status de aprovação
- Variáveis dinâmicas por template

## 5. Comunicação em Tempo Real

### 5.1 Tecnologia

- Laravel WebSockets para comunicação em tempo real
- Broadcasting de eventos Laravel
- Componentes Vue.js para atualização da interface

### 5.2 Funcionalidades

- Notificação de novas mensagens
- Indicador de digitação
- Atualização do status de atendimento
- Notificações para atendentes

### 5.3 Implementação

- Servidor WebSockets dedicado por cliente
- Autenticação de canais privados
- Otimização para baixo consumo de recursos

## 6. Integração com API FILA-IA

### 6.1 Visão Geral da API FILA-IA

A API FILA-IA é um serviço intermediário que gerencia requisições ao modelo Llama3 8B via Ollama, fornecendo recursos de filas, autenticação e resiliência. O sistema SaaS de Atendimento WhatsApp se integra a esta API para obter respostas automatizadas quando necessário.

### 6.2 Autenticação e Configuração

- Cada instância do sistema terá uma chave de API FILA-IA exclusiva
- A chave é armazenada de forma segura e criptografada
- Configurações de uso da IA personalizáveis por cliente:
  - Temperatura (aleatoriedade das respostas)
  - Limite máximo de tokens
  - Regras para decidir quando usar a IA

### 6.3 Fluxo de Integração

1. Sistema recebe mensagem do WhatsApp
2. Baseado em regras configuráveis, decide se deve usar IA
3. Se IA for necessária, envia requisição para API FILA-IA:
   ```json
   {
     "prompt": "Contexto da conversa + mensagem do usuário",
     "parameters": {
       "temperature": 0.7,
       "max_tokens": 100
     },
     "metadata": {
       "user_id": "id_do_cliente",
       "session_id": "id_da_conversa"
     }
   }
   ```
4. Armazena o ID da requisição retornado pela API
5. Verifica periodicamente o status da requisição (com backoff exponencial)
6. Quando a resposta estiver pronta, envia para o cliente via WhatsApp
7. Registra a interação para análise e melhoria contínua

### 6.4 Tratamento de Erros e Resiliência

- Implementação de retry com backoff exponencial
- Circuit breaker para evitar sobrecarga durante falhas
- Fallback para atendimento humano quando:
  - API FILA-IA não responde em tempo hábil
  - Erro é retornado pela API
  - Limite de requisições é excedido

### 6.5 Monitoramento e Limites

- Interface para visualizar uso da API por cliente
- Alertas para quando limites estão próximos de serem atingidos
- Configuração de limites personalizados por cliente:
  - Requisições por minuto
  - Requisições por hora
  - Requisições por dia

### 6.6 Exemplo de Implementação

```php
// Exemplo simplificado de integração com API FILA-IA
public function sendToIA($message, $conversationContext)
{
    $apiKey = config('fila_ia.api_key');
    $baseUrl = config('fila_ia.base_url');
    
    // Formatar prompt com contexto
    $prompt = $this->formatPromptWithContext($message, $conversationContext);
    
    // Enviar requisição
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'X-API-Key' => $apiKey
    ])->post($baseUrl . '/requests', [
        'prompt' => $prompt,
        'parameters' => [
            'temperature' => 0.7,
            'max_tokens' => 200
        ],
        'metadata' => [
            'user_id' => $message->from,
            'session_id' => $message->conversation_id
        ]
    ]);
    
    if ($response->successful()) {
        $requestId = $response->json('id');
        
        // Enfileirar job para verificar status periodicamente
        CheckIAResponseJob::dispatch($requestId)
            ->delay(now()->addSeconds(2));
            
        return $requestId;
    }
    
    // Tratar erro
    Log::error('Erro ao enviar requisição para FILA-IA', [
        'status' => $response->status(),
        'response' => $response->json()
    ]);
    
    return null;
}
```

## 7. Sistema de Backup

### 7.1 Estratégia de Backup

- Backup diário automático
- Retenção configurável (7 dias, 30 dias)
- Armazenamento local ou em nuvem

### 7.2 Componentes do Backup

- Banco de dados completo
- Arquivos de mídia
- Configurações e templates

### 7.3 Processo de Restauração

- Interface administrativa para restauração
- Log detalhado de operações de restauração

## 8. Segurança

### 8.1 Proteção de Dados

- TLS/SSL para todas as comunicações
- Criptografia de dados sensíveis em repouso
- Sanitização de inputs para prevenção de injeções
- Proteção contra XSS e CSRF

### 8.2 Auditoria e Logs

- Registro detalhado de todas as ações de usuários
- Logs de acesso com IP, dispositivo e localização
- Retenção de logs configurável

### 8.3 Controle de Acesso

- RBAC (Role-Based Access Control)
- Permissões granulares por funcionalidade
- Timeout de sessão configurável
- Bloqueio após tentativas de login malsucedidas

## 9. Considerações de Implementação

### 9.1 Cronograma Estimado

- **Fase 1 (1 mês)**: 
  - Sistema base e autenticação (1 semana)
  - Painel administrativo (1 semana)
  - Integração WhatsApp básica (2 semanas)
  
- **Fase 2 (2 semanas)**:
  - Comunicação em tempo real (1 semana)
  - Integração com API FILA-IA (1 semana)
  
- **Fase 3 (1 semana)**:
  - Sistema de backup
  - Segurança e conformidade LGPD

### 9.2 Equipe Recomendada

- 1 Desenvolvedor Backend (Laravel)
- 1 Desenvolvedor Frontend (Vue.js/Blade)
- 1 DevOps (meio período)

### 9.3 Ambiente de Desenvolvimento

- Docker Compose para ambiente local
- Repositório Git para controle de versão
- Ambientes separados (dev, produção)

### 9.4 Processo de Implantação para Novos Clientes

1. Criar nova instância dos containers Docker
2. Configurar domínio específico
3. Configurar credenciais da API WhatsApp
4. Configurar chave da API FILA-IA
5. Criar usuários iniciais
6. Realizar testes de integração

## 10. Conclusão

O Sistema de Atendimento WhatsApp representa uma solução simplificada e eficiente para empresas que desejam oferecer atendimento via WhatsApp. A arquitetura com containers isolados por cliente garante segurança e independência, enquanto a comunicação em tempo real proporciona uma experiência de atendimento fluida. A integração com a API FILA-IA adiciona capacidades de automação, permitindo respostas rápidas e consistentes para perguntas frequentes.

Esta abordagem simplificada permite uma implementação mais rápida e manutenção mais simples, mantendo as funcionalidades essenciais para um sistema de atendimento profissional via WhatsApp.

### 10.1 Próximos Passos

- Desenvolvimento do sistema base
- Configuração da infraestrutura Docker
- Implementação da integração com WhatsApp
- Implementação da integração com API FILA-IA
- Desenvolvimento da interface de atendimento com comunicação em tempo real 