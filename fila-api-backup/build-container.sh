#!/bin/bash

# Script para construir e iniciar os containers Docker sem links simbólicos
# Autor: [Seu Nome]
# Data: $(date +%Y-%m-%d)

echo "=== Iniciando construção dos containers Docker ==="

# Parar containers existentes se estiverem rodando
echo "Parando containers existentes..."
docker-compose down

# Construir as imagens
echo "Construindo imagens Docker..."
docker-compose build --no-cache

# Iniciar os containers
echo "Iniciando containers..."
docker-compose up -d

# Verificar status
echo "Verificando status dos containers..."
docker-compose ps

echo "=== Processo concluído! ==="
echo "O sistema FILA-API está rodando em http://localhost:8000"
echo "Para visualizar logs: docker-compose logs -f" 