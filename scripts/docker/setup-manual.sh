#!/bin/bash

# Script para configurar manualmente o ambiente FILA-IA no Proxmox
# Autor: Claude
# Data: 01/07/2025

echo "=== Configurando manualmente o ambiente FILA-IA no Proxmox ==="
echo

# Criar diretório de instalação
echo "Criando diretório de instalação..."
sudo mkdir -p /var/www/fila-ia
sudo chown sales:sales /var/www/fila-ia
cd /var/www/fila-ia

# Extrair os arquivos
echo "Extraindo arquivos..."
echo "Copiando arquivos do diretório home..."
cp ~/fila-ia-full.tar.gz .
cp ~/fila-ia-docker.tar.gz .

echo "Extraindo fila-ia-full.tar.gz..."
tar -xzvf fila-ia-full.tar.gz

echo "Extraindo fila-ia-docker.tar.gz..."
tar -xzvf fila-ia-docker.tar.gz

echo "Listando arquivos extraídos..."
ls -la

# Verificar se o docker-compose.yml existe
if [ ! -f "docker-compose.yml" ]; then
    echo "ERRO: docker-compose.yml não encontrado!"
    echo "Tentando extrair diretamente do arquivo tar.gz..."
    
    # Tentar extrair apenas o docker-compose.yml
    tar -xzvf ~/fila-ia-docker.tar.gz docker-compose.yml
    
    if [ ! -f "docker-compose.yml" ]; then
        echo "ERRO: Não foi possível encontrar docker-compose.yml"
        echo "Vamos criar um manualmente..."
        
        cat > docker-compose.yml << 'EOL'
version: '3'

services:
  fila-nginx:
    image: nginx:alpine
    container_name: fila-nginx
    ports:
      - "8000:80"
    volumes:
      - ./docker/nginx:/etc/nginx/conf.d
      - ./public:/var/www/html/public
    depends_on:
      - fila-api
    networks:
      - fila-network

  fila-api:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: fila-api
    volumes:
      - ./:/var/www/html
    depends_on:
      - fila-db
      - fila-redis
    networks:
      - fila-network
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:80"]
      interval: 10s
      timeout: 5s
      retries: 3

  fila-db:
    image: mysql:8.0
    container_name: fila-db
    environment:
      MYSQL_DATABASE: fila_api
      MYSQL_USER: fila
      MYSQL_PASSWORD: fila
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - fila-db-data:/var/lib/mysql
    networks:
      - fila-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p$$MYSQL_ROOT_PASSWORD"]
      interval: 10s
      timeout: 5s
      retries: 3

  fila-redis:
    image: redis:alpine
    container_name: fila-redis
    networks:
      - fila-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 3

networks:
  fila-network:
    driver: bridge

volumes:
  fila-db-data:
EOL
    fi
fi

# Verificar se o Dockerfile existe
if [ ! -f "Dockerfile" ]; then
    echo "ERRO: Dockerfile não encontrado!"
    echo "Criando Dockerfile..."
    
    cat > Dockerfile << 'EOL'
FROM php:8.2-fpm

# Argumentos
ARG user=www-data
ARG uid=1000

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    libzip-dev

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Redis
RUN pecl install redis && docker-php-ext-enable redis

# Configurar o PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . /var/www/html

# Configurar permissões
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar Supervisor
COPY docker/supervisord/horizon.conf /etc/supervisor/conf.d/horizon.conf

# Expor porta
EXPOSE 9000

# Iniciar serviços
CMD ["/bin/bash", "/var/www/html/docker/start.sh"]
EOL
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

# Verificar se o arquivo .env.example existe
if [ ! -f ".env.example" ]; then
    echo "ERRO: .env.example não encontrado!"
    echo "Criando arquivo .env..."
    
    cat > .env << 'EOL'
APP_NAME=FILA-API
APP_ENV=production
APP_KEY=
APP_DEBUG=false
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

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Configuração do Ollama
OLLAMA_API_URL=http://host.docker.internal:11434
OLLAMA_MODEL=llama3:8b
OLLAMA_TIMEOUT=60

# Configurações de Resiliência
CIRCUIT_BREAKER_THRESHOLD=5
CIRCUIT_BREAKER_TIMEOUT=60
EOL
else
    echo "Copiando .env.example para .env..."
    cp .env.example .env
fi

# Configurar variáveis de ambiente
echo "Configurando variáveis de ambiente..."
sed -i 's/DB_HOST=.*/DB_HOST=fila-db/' .env
sed -i 's/REDIS_HOST=.*/REDIS_HOST=fila-redis/' .env
sed -i 's/OLLAMA_API_URL=.*/OLLAMA_API_URL=http:\/\/host.docker.internal:11434/' .env
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env

# Verificar se o diretório docker/nginx existe
if [ ! -d "docker/nginx" ]; then
    echo "Criando configuração do Nginx..."
    mkdir -p docker/nginx
    
    cat > docker/nginx/default.conf << 'EOL'
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass fila-api:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOL
fi

# Verificar se o diretório docker/supervisord existe
if [ ! -d "docker/supervisord" ]; then
    echo "Criando configuração do Supervisor..."
    mkdir -p docker/supervisord
    
    cat > docker/supervisord/horizon.conf << 'EOL'
[program:horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/horizon.log
stopwaitsecs=3600
EOL
fi

# Verificar se o arquivo docker/start.sh existe
if [ ! -f "docker/start.sh" ]; then
    echo "Criando script de inicialização..."
    mkdir -p docker
    
    cat > docker/start.sh << 'EOL'
#!/bin/bash

# Iniciar PHP-FPM em background
php-fpm -D

# Iniciar Supervisor para gerenciar Horizon
supervisord -c /etc/supervisor/supervisord.conf

# Manter o container rodando
tail -f /dev/null
EOL

    chmod +x docker/start.sh
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
echo "Acesse o sistema em: http://$(hostname -I | awk '{print $1}'):8000"
echo "Para verificar o status dos containers, execute: docker-compose ps" 