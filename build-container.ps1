# Script para construir e iniciar os containers Docker sem links simbólicos
# Autor: [Seu Nome]
# Data: $(Get-Date -Format "yyyy-MM-dd")

Write-Host "=== Iniciando construção dos containers Docker ===" -ForegroundColor Green

# Parar containers existentes se estiverem rodando
Write-Host "Parando containers existentes..." -ForegroundColor Yellow
docker-compose down

# Construir as imagens
Write-Host "Construindo imagens Docker..." -ForegroundColor Yellow
docker-compose build --no-cache

# Iniciar os containers
Write-Host "Iniciando containers..." -ForegroundColor Yellow
docker-compose up -d

# Verificar status
Write-Host "Verificando status dos containers..." -ForegroundColor Yellow
docker-compose ps

Write-Host "=== Processo concluído! ===" -ForegroundColor Green
Write-Host "O sistema FILA-API está rodando em http://localhost:8000" -ForegroundColor Cyan
Write-Host "Para visualizar logs: docker-compose logs -f" -ForegroundColor Cyan 