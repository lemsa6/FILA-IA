# Verificar se o Docker está instalado
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Error "Docker não está instalado ou não está no PATH."
    exit 1
}

# Verificar se o Docker Compose está instalado
if (-not (Get-Command docker-compose -ErrorAction SilentlyContinue)) {
    Write-Error "Docker Compose não está instalado ou não está no PATH."
    exit 1
}

# Verificar se a rede fila-network existe
$filaNetwork = docker network ls | Select-String -Pattern "fila-ia_fila-network"
if (-not $filaNetwork) {
    Write-Error "A rede fila-ia_fila-network não existe. Verifique se o projeto FILA-IA está em execução."
    exit 1
}

# Verificar se o container fila-redis está em execução
$filaRedis = docker ps | Select-String -Pattern "fila-redis"
if (-not $filaRedis) {
    Write-Error "O container fila-redis não está em execução. Verifique se o projeto FILA-IA está em execução."
    exit 1
}

Write-Host "Construindo e iniciando containers do SaaS WhatsApp..." -ForegroundColor Green

# Construir e iniciar os containers
docker-compose up -d --build

# Verificar se os containers foram iniciados corretamente
if ($LASTEXITCODE -eq 0) {
    Write-Host "Containers iniciados com sucesso!" -ForegroundColor Green
    Write-Host "Acesse o sistema em http://localhost:8080" -ForegroundColor Cyan
} else {
    Write-Error "Ocorreu um erro ao iniciar os containers."
    exit 1
}

# Copiar o arquivo env.example para .env se não existir
if (-not (Test-Path -Path ".env")) {
    Write-Host "Copiando arquivo env.example para .env..." -ForegroundColor Yellow
    Copy-Item -Path "env.example" -Destination ".env"
}

# Instalar dependências e configurar o projeto
Write-Host "Instalando dependências e configurando o projeto..." -ForegroundColor Green
docker-compose exec saas-api composer install
docker-compose exec saas-api php artisan key:generate
docker-compose exec saas-api php artisan migrate --seed
docker-compose exec saas-api php artisan optimize

Write-Host "Sistema SaaS WhatsApp configurado e pronto para uso!" -ForegroundColor Green 