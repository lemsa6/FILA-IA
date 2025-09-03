# 🧠 Sistema de Cache Inteligente - FILA-IA

## **📋 Visão Geral**

O sistema de cache inteligente permite que cada cliente tenha:
- **Contexto base persistente** (produto/serviço)
- **Histórico de conversa** incremental
- **Cache otimizado** por cliente
- **Isolamento completo** entre clientes

---

## **🚀 Como Funciona**

### **1. Contexto Base (Produto/Serviço)**
```php
// Define uma vez, usado sempre
$baseContext = "Somos uma clínica de cardiologia especializada...";
$intelligentAIService->setBaseContext($apiKeyId, $baseContext);
```

### **2. Conversa Incremental**
```php
// Primeira pergunta: usa contexto base
$response1 = $intelligentAIService->generateIntelligentResponse(
    "Quais exames vocês oferecem?",
    $apiKeyId,
    "session-123"
);

// Segunda pergunta: usa contexto base + primeira conversa
$response2 = $intelligentAIService->generateIntelligentResponse(
    "Qual o valor?",
    $apiKeyId,
    "session-123" // Mesma sessão!
);
```

### **3. Cache Inteligente**
- **Contexto base**: Cache por 30 dias
- **Histórico conversa**: Cache por 24 horas
- **Respostas**: Cache por 1 hora
- **Isolamento**: Cada cliente tem seu próprio cache

---

## **🔧 API Endpoints**

### **Definir Contexto Base**
```http
POST /api/v1/context/base
Content-Type: application/json
Authorization: Bearer {api_key}

{
    "base_context": "Texto do contexto do produto/serviço...",
    "metadata": {
        "domain": "medicina",
        "specialty": "cardiologia"
    }
}
```

### **Obter Contexto Base**
```http
GET /api/v1/context/base
Authorization: Bearer {api_key}
```

### **Atualizar Contexto Base**
```http
PUT /api/v1/context/base
Content-Type: application/json
Authorization: Bearer {api_key}

{
    "base_context": "Novo contexto atualizado...",
    "metadata": {
        "version": "2.0"
    }
}
```

### **Remover Contexto Base**
```http
DELETE /api/v1/context/base
Authorization: Bearer {api_key}
```

### **Estatísticas de Cache**
```http
GET /api/v1/cache/stats
Authorization: Bearer {api_key}
```

---

## **💡 Casos de Uso**

### **1. Clínica Médica**
```php
// Contexto base (definido uma vez)
$baseContext = "Somos a Clínica CardioVida, especializada em cardiologia.
Oferecemos consultas, exames cardíacos, ecocardiogramas e tratamentos.
Nossa equipe tem 15 anos de experiência e utiliza tecnologia de ponta.
Endereço: Rua das Flores, 123 - Centro.
Telefone: (11) 99999-9999.
Horário: Segunda a Sexta, 8h às 18h.";

$intelligentAIService->setBaseContext($apiKeyId, $baseContext);
```

### **2. E-commerce**
```php
// Contexto base (definido uma vez)
$baseContext = "Somos a TechStore, loja especializada em produtos tecnológicos.
Trabalhamos com marcas como Apple, Samsung, Dell, HP.
Oferecemos frete grátis para compras acima de R$ 299.
Garantia de 12 meses em todos os produtos.
Atendimento via WhatsApp: (11) 88888-8888.";

$intelligentAIService->setBaseContext($apiKeyId, $baseContext);
```

---

## **🔍 Monitoramento e Estatísticas**

### **Comando de Teste**
```bash
# Testa todo o sistema
php artisan ai:test-cache

# Testa com API key específica
php artisan ai:test-cache {api_key_id}
```

### **Estatísticas via API**
```json
{
    "cache_stats": {
        "base_context": {
            "has_context": true,
            "content_length": 1250,
            "estimated_tokens": 313,
            "last_updated": "2024-01-15T10:30:00Z"
        },
        "sessions": {
            "active_count": 3,
            "total_interactions": 15
        },
        "cache_efficiency": {
            "base_context_used": true,
            "conversation_context_used": true
        }
    }
}
```

---

## **⚡ Vantagens do Sistema**

### **1. Economia de Tokens**
- **Antes**: Enviava contexto + conversa a cada pergunta
- **Agora**: Contexto base é reutilizado automaticamente

### **2. Respostas Mais Precisas**
- IA sempre tem contexto completo
- Histórico da conversa mantido
- Continuidade entre perguntas

### **3. Isolamento Total**
- Cada cliente tem seu próprio contexto
- Cache não se mistura entre clientes
- Sessões independentes

### **4. Performance**
- Cache Redis para velocidade
- TTL inteligente para cada tipo de dado
- Fallback automático em caso de falha

---

## **🛠️ Configuração**

### **Variáveis de Ambiente**
```env
# Cache
CACHE_DRIVER=redis
REDIS_HOST=fila-redis
REDIS_PORT=6379

# Ollama
OLLAMA_API_URL=http://host.docker.internal:11434
OLLAMA_MODEL=llama3:8b
OLLAMA_CACHE_TTL=3600
```

### **Dependências**
- Redis (cache e sessões)
- Ollama (IA local)
- Laravel 12
- PHP 8.2+

---

## **🚨 Limitações e Considerações**

### **1. Tamanho do Contexto**
- **Máximo**: 50KB por contexto base
- **Recomendado**: 2-5KB para melhor performance
- **Tokens estimados**: ~1 token = 4 caracteres

### **2. Histórico de Conversa**
- **Máximo**: 20 interações por sessão
- **Cache**: 24 horas por sessão
- **Limpeza automática**: Comando `sessions:cleanup`

### **3. Performance**
- **Contexto base**: Carregado uma vez por cliente
- **Histórico**: Incremental (apenas novas interações)
- **Cache**: Redis para velocidade máxima

---

## **🔮 Futuras Melhorias**

### **1. Suporte a GPT-5**
- Estrutura de messages
- Cache control avançado
- Rate limiting por modelo

### **2. Cache Inteligente**
- Compressão de contexto
- Priorização de informações
- Limpeza automática inteligente

### **3. Analytics**
- Métricas de uso por cliente
- Análise de eficiência do cache
- Relatórios de performance

---

## **📞 Suporte**

Para dúvidas ou problemas:
- **Documentação**: Este arquivo
- **Comando de teste**: `php artisan ai:test-cache`
- **Logs**: `storage/logs/laravel.log`
- **Redis**: `docker exec -it fila-redis redis-cli`

---

**🎉 Sistema implementado e funcionando perfeitamente!**
