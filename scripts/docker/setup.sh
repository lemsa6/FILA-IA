#!/bin/bash

# Construir e iniciar os containers
docker-compose up -d --build

# Esperar o container do MySQL inicializar completamente
echo "Aguardando o MySQL inicializar..."
sleep 10

# Entrar no container da API e instalar o Laravel
docker-compose exec fila-api bash -c "composer create-project laravel/laravel ."

# Criar arquivo .env com as configurações corretas
cat > .env << EOL
APP_NAME=FILA-API
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=fila-db
DB_PORT=3306
DB_DATABASE=fila_api
DB_USERNAME=fila
DB_PASSWORD=fila

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=fila-redis
REDIS_PASSWORD=null
REDIS_PORT=6379

OLLAMA_API_URL=http://host.docker.internal:11434/api
OLLAMA_MODEL=gemma3:12b
EOL

docker-compose exec fila-api bash -c "cp /var/www/html/.env /var/www/html/.env.example"

# Gerar a chave da aplicação
docker-compose exec fila-api bash -c "php artisan key:generate"

# Instalar o Laravel Horizon
docker-compose exec fila-api bash -c "composer require laravel/horizon"

# Publicar os assets do Horizon
docker-compose exec fila-api bash -c "php artisan horizon:install"

# Instalar o Laravel Telescope para monitoramento
docker-compose exec fila-api bash -c "composer require laravel/telescope --dev"

# Publicar os assets do Telescope
docker-compose exec fila-api bash -c "php artisan telescope:install"

# Executar as migrações
docker-compose exec fila-api bash -c "php artisan migrate"

# Configurar permissões
docker-compose exec fila-api bash -c "chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache"

echo "Instalação concluída! Acesse http://localhost:8000" 