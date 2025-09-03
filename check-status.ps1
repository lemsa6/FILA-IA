# Script para verificar o status dos containers e visualizar logs
# Autor: [Seu Nome]
# Data: $(Get-Date -Format "yyyy-MM-dd")

param(
    [switch]$logs,
    [string]$service = ""
)

Write-Host "=== Verificando status do sistema FILA-API ===" -ForegroundColor Green

# Verificar status dos containers
Write-Host "Status dos containers:" -ForegroundColor Yellow
docker-compose ps

# Verificar uso de recursos
Write-Host "Uso de recursos:" -ForegroundColor Yellow
docker stats --no-stream

# Verificar logs se solicitado
if ($logs) {
    if ($service -ne "") {
        Write-Host "Exibindo logs do serviço $service:" -ForegroundColor Yellow
        docker-compose logs -f $service
    } else {
        Write-Host "Exibindo logs de todos os serviços:" -ForegroundColor Yellow
        docker-compose logs -f
    }
}

Write-Host "Para visualizar logs de um serviço específico:" -ForegroundColor Cyan
Write-Host ".\check-status.ps1 -logs -service fila-api" -ForegroundColor Cyan 