# FILA-IA

Sistema de filas inteligente para processamento de requisições GPT com Laravel Horizon e Redis.

## 🚀 Versão 0.1.1

### ✨ Funcionalidades

- **Processamento de Filas**: Sistema robusto de filas com Laravel Horizon
- **Integração GPT**: Processamento de requisições OpenAI GPT-4
- **Cache Inteligente**: Sistema de cache por cliente para otimização
- **Circuit Breaker**: Proteção contra falhas de serviços externos
- **Docker**: Containerização completa do sistema
- **Monitoramento**: Logs detalhados e métricas de performance

### 🏗️ Arquitetura

- **Backend**: Laravel 10
- **Filas**: Redis + Laravel Horizon
- **Banco**: MySQL
- **Cache**: Redis
- **Container**: Docker + Docker Compose

### 📋 Requisitos

- Docker
- Docker Compose
- PHP 8.1+
- MySQL 8.0+
- Redis 6.0+

### 🚀 Instalação

```bash
# Clone o repositório
git clone https://github.com/lemsa6/FILA-IA.git
cd FILA-IA

# Inicie os containers
docker-compose up -d

# Execute as migrações
docker exec fila-api php artisan migrate

# Inicie o Horizon
docker exec fila-api php artisan horizon
```

### 📊 Monitoramento

- **Horizon Dashboard**: http://localhost:8000/horizon
- **Logs**: `storage/logs/laravel.log`
- **Métricas**: Redis + Horizon

### 🔧 Configuração

Configure as variáveis de ambiente no arquivo `.env`:

```env
OPENAI_API_KEY=sua_chave_openai
REDIS_HOST=fila-redis
DB_HOST=fila-db
```

### 📈 Performance

- **Tempo de Processamento**: 3-5 segundos por requisição
- **Throughput**: Até 15 workers simultâneos
- **Cache Hit Rate**: 60-80% para requisições similares

### 🛠️ Desenvolvimento

```bash
# Executar testes
docker exec fila-api php artisan test

# Verificar status das filas
docker exec fila-api php artisan queue:failed

# Monitorar logs
docker exec fila-api tail -f storage/logs/laravel.log
```

### 📝 Changelog

#### v0.1.1 (2025-08-28)
- ✅ Adicionada coluna `started_at` na tabela `requests`
- ✅ Corrigidos jobs falhando por schema incompleto
- ✅ Otimizado processamento de filas
- ✅ Melhorada confiabilidade do sistema

### 📄 Licença

MIT License

### 👥 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

---

**Desenvolvido por lemsa6** | [GitHub](https://github.com/lemsa6/FILA-IA)