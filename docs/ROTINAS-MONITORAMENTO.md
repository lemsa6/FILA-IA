# 🔄 Sistema de Rotinas - Monitoramento FILA-IA

## **📋 Visão Geral**

O sistema de rotinas é um dashboard centralizado para monitoramento e testes do sistema FILA-IA, permitindo verificar a saúde de todos os componentes em tempo real.

---

## **🚀 Funcionalidades Principais**

### **1. 🔍 Status Geral do Sistema**
- **Ollama**: Health check da IA
- **Redis**: Status do cache e sessões
- **Database**: Conexão MySQL
- **Status Geral**: Visão consolidada

### **2. 🧪 Testes Integrados**
- **Teste Ollama**: API, health check e performance
- **Teste Cache Inteligente**: Contexto base e histórico
- **Testes de Performance**: Múltiplas requisições simultâneas

### **3. 📊 Estatísticas em Tempo Real**
- **Requisições**: Total, pendentes, concluídas, taxa de sucesso
- **API Keys**: Total e ativas
- **Cache**: Driver e configurações

---

## **🔧 Como Acessar**

### **Menu Principal**
```
Dashboard → 🔄 Rotinas
```

### **URL Direta**
```
/admin/routines
```

---

## **🧪 Tipos de Testes**

### **1. 🤖 Teste Ollama**
**O que testa:**
- ✅ Health check do serviço
- ✅ Resposta da API
- ✅ Performance (3 requisições simultâneas)
- ✅ Tempo de resposta médio

**Como executar:**
1. Clique em "🧪 Executar Teste Ollama"
2. Aguarde o processamento
3. Visualize os resultados detalhados

**Resultados esperados:**
```json
{
    "success": true,
    "tests": {
        "health_check": true,
        "api_test": true,
        "performance_test": true
    },
    "details": {
        "api_response_time": "1250.45 ms",
        "performance": {
            "success_rate": "100%",
            "average_response_time": "1200.33 ms"
        }
    }
}
```

### **2. 🧠 Teste Cache Inteligente**
**O que testa:**
- ✅ Setup de contexto base
- ✅ Primeira resposta com contexto
- ✅ Segunda resposta (deve usar cache)
- ✅ Estatísticas de cache
- ✅ Limpeza automática

**Como executar:**
1. Clique em "🧪 Executar Teste Cache"
2. Aguarde o processamento
3. Visualize os resultados detalhados

**Resultados esperados:**
```json
{
    "success": true,
    "tests": {
        "context_setup": true,
        "first_response": true,
        "second_response": true
    },
    "details": {
        "first_response_time": "1500.25 ms",
        "second_response_time": "800.12 ms",
        "session_id": "test-session-1234567890"
    }
}
```

---

## **📊 Monitoramento em Tempo Real**

### **Status dos Serviços**
- **🟢 Verde**: Serviço funcionando
- **🔴 Vermelho**: Serviço com problema
- **🔄 Botão**: Atualizar status manualmente

### **Indicadores de Teste**
- **🟢 Verde**: Teste passou
- **🔴 Vermelho**: Teste falhou
- **⭕ Cinza**: Teste não executado

---

## **🔍 Endpoints da API**

### **Status do Sistema**
```http
GET /admin/routines/system-status
```

**Resposta:**
```json
{
    "success": true,
    "overall_health": true,
    "services": {
        "ollama": {
            "healthy": true,
            "model": "llama3:8b",
            "url": "http://host.docker.internal:11434"
        },
        "redis": {
            "healthy": true,
            "host": "fila-redis",
            "port": 6379
        },
        "database": {
            "healthy": true,
            "connection": "mysql",
            "host": "fila-db"
        }
    },
    "statistics": {
        "requests": {
            "total": 150,
            "pending": 5,
            "completed": 140,
            "success_rate": "93.33%"
        }
    }
}
```

### **Teste Ollama**
```http
POST /admin/routines/test-ollama
```

### **Teste Cache Inteligente**
```http
POST /admin/routines/test-intelligent-cache
```

---

## **💡 Casos de Uso**

### **1. 🚨 Monitoramento Diário**
- Verificar status dos serviços ao iniciar o dia
- Executar testes rápidos para validar funcionamento
- Acompanhar estatísticas de uso

### **2. 🔧 Troubleshooting**
- Identificar qual serviço está com problema
- Executar testes específicos para diagnóstico
- Verificar logs de erro

### **3. 📈 Análise de Performance**
- Monitorar tempos de resposta
- Acompanhar taxa de sucesso
- Identificar gargalos

### **4. 🧪 Validação de Deploy**
- Testar sistema após atualizações
- Validar configurações
- Verificar integrações

---

## **⚡ Vantagens do Sistema**

### **1. 🎯 Centralização**
- Todos os testes em um local
- Interface unificada
- Histórico consolidado

### **2. 🚀 Automação**
- Testes com um clique
- Resultados em tempo real
- Logs automáticos

### **3. 📊 Visibilidade**
- Status visual claro
- Métricas detalhadas
- Indicadores de saúde

### **4. 🔄 Atualização em Tempo Real**
- Status automático
- Botão de refresh manual
- Dados sempre atualizados

---

## **🚨 Troubleshooting**

### **Problema: Teste não executa**
**Soluções:**
1. Verificar se todos os serviços estão rodando
2. Verificar logs em `storage/logs/laravel.log`
3. Verificar permissões de usuário
4. Verificar configurações do `.env`

### **Problema: Status sempre vermelho**
**Soluções:**
1. Verificar se Docker está rodando
2. Verificar conectividade entre containers
3. Verificar configurações de rede
4. Verificar logs dos serviços

### **Problema: Testes falham**
**Soluções:**
1. Verificar se Ollama está rodando
2. Verificar se Redis está acessível
3. Verificar se MySQL está funcionando
4. Verificar logs específicos do teste

---

## **🔮 Futuras Melhorias**

### **1. 📈 Dashboard Avançado**
- Gráficos de performance
- Histórico de testes
- Alertas automáticos
- Relatórios periódicos

### **2. 🤖 Testes Automatizados**
- Execução automática
- Agendamento de testes
- Notificações por email
- Integração com CI/CD

### **3. 📱 Monitoramento Mobile**
- App mobile para monitoramento
- Push notifications
- Status offline
- Sincronização automática

---

## **📞 Suporte**

Para problemas ou dúvidas:
- **Documentação**: Este arquivo
- **Logs**: `storage/logs/laravel.log`
- **Dashboard**: `/admin/routines`
- **Comandos**: `php artisan ai:test-cache`

---

**🎉 Sistema de rotinas implementado e funcionando perfeitamente!**

**Acesse `/admin/routines` para começar a monitorar seu sistema!** 🚀
