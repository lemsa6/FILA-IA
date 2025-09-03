#!/bin/bash

# Script para configurar o ambiente FILA-IA no Proxmox
# Autor: Claude
# Data: 01/07/2025

echo "=== Configurando ambiente FILA-IA no Proxmox ==="
echo

# Criar diretório de instalação
echo "Criando diretório de instalação..."
sudo mkdir -p /var/www/fila-ia
sudo chown $(whoami):$(whoami) /var/www/fila-ia
cd /var/www/fila-ia

# Extrair arquivos do projeto
echo "Extraindo arquivos do projeto..."
cp ~/fila-ia-full.tar.gz .
cp ~/fila-ia-docker.tar.gz .
tar -xzvf fila-ia-full.tar.gz
tar -xzvf fila-ia-docker.tar.gz

# Verificar se os arquivos foram extraídos corretamente
echo "Verificando arquivos extraídos..."
if [ ! -f "docker-compose.yml" ]; then
    echo "ERRO: docker-compose.yml não encontrado!"
    ls -la
    exit 1
fi

# Criar diretórios necessários
echo "Criando diretórios necessários..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Configurar permissões
echo "Configurando permissões..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Configurar variáveis de ambiente
echo "Configurando variáveis de ambiente..."
if [ -f ".env.example" ]; then
    cp .env.example .env
    sed -i 's/DB_HOST=.*/DB_HOST=fila-db/' .env
    sed -i 's/REDIS_HOST=.*/REDIS_HOST=fila-redis/' .env
    sed -i 's/OLLAMA_API_URL=.*/OLLAMA_API_URL=http:\/\/host.docker.internal:11434/' .env
    sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
    sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
else
    echo "ERRO: .env.example não encontrado!"
    exit 1
fi

# Iniciar containers
echo "Iniciando containers..."
docker-compose up -d

# Aguardar containers iniciarem
echo "Aguardando containers iniciarem..."
sleep 10

# Instalar dependências e configurar o Laravel
echo "Instalando dependências e configurando o Laravel..."
docker-compose exec -T fila-api composer install --no-dev --optimize-autoloader
docker-compose exec -T fila-api php artisan key:generate
docker-compose exec -T fila-api php artisan config:cache
docker-compose exec -T fila-api php artisan route:cache
docker-compose exec -T fila-api php artisan view:cache

# Executar migrações
echo "Executando migrações..."
docker-compose exec -T fila-api php artisan migrate --force

# Executar seeders
echo "Executando seeders..."
docker-compose exec -T fila-api php artisan db:seed --force

echo
echo "=== Instalação concluída! ==="
echo "Acesse o sistema em: http://localhost:8000"
echo "Para verificar o status dos containers, execute: docker-compose ps" 