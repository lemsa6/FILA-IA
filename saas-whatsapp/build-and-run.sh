#!/bin/bash

# Verificar se o Docker está instalado
if ! [ -x "$(command -v docker)" ]; then
  echo 'Erro: Docker não está instalado.' >&2
  exit 1
fi

# Verificar se o Docker Compose está instalado
if ! [ -x "$(command -v docker-compose)" ]; then
  echo 'Erro: Docker Compose não está instalado.' >&2
  exit 1
fi

# Verificar se a rede fila-network existe
if ! docker network ls | grep -q fila-ia_fila-network; then
  echo "A rede fila-ia_fila-network não existe. Verifique se o projeto FILA-IA está em execução."
  exit 1
fi

# Verificar se o container fila-redis está em execução
if ! docker ps | grep -q fila-redis; then
  echo "O container fila-redis não está em execução. Verifique se o projeto FILA-IA está em execução."
  exit 1
fi

echo "Construindo e iniciando containers do SaaS WhatsApp..."

# Construir e iniciar os containers
docker-compose up -d --build

# Verificar se os containers foram iniciados corretamente
if [ $? -eq 0 ]; then
  echo "Containers iniciados com sucesso!"
  echo "Acesse o sistema em http://localhost:8080"
else
  echo "Ocorreu um erro ao iniciar os containers."
  exit 1
fi

# Copiar o arquivo env.example para .env se não existir
if [ ! -f .env ]; then
  echo "Copiando arquivo env.example para .env..."
  cp env.example .env
fi

# Instalar dependências e configurar o projeto
echo "Instalando dependências e configurando o projeto..."
docker-compose exec saas-api composer install
docker-compose exec saas-api php artisan key:generate
docker-compose exec saas-api php artisan migrate --seed
docker-compose exec saas-api php artisan optimize

echo "Sistema SaaS WhatsApp configurado e pronto para uso!" 