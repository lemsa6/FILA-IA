# FILA-API: Configuração Docker Sem Links Simbólicos

Este documento explica como configurar e executar o sistema FILA-API em containers Docker sem utilizar links simbólicos para os arquivos locais.

## Requisitos

- Docker
- Docker Compose
- PowerShell (para Windows) ou Bash (para Linux/Mac)

## Arquivos Importantes

- `docker-compose.yml`: Configuração dos serviços Docker
- `Dockerfile`: Instruções para construir a imagem do container
- `build-container.ps1` (Windows): Script para construir e iniciar os containers
- `build-container.sh` (Linux/Mac): Script para construir e iniciar os containers
- `check-status.ps1` (Windows): Script para verificar o status dos containers
- `.env.docker`: Arquivo de ambiente para o container

## Instruções de Uso

### 1. Backup

Antes de iniciar, é recomendável fazer um backup do projeto:

```powershell
# Windows
Compress-Archive -Path * -DestinationPath "FILA-IA_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').zip" -Force

# Linux/Mac
tar -czf "FILA-IA_backup_$(date +%Y%m%d_%H%M%S).tar.gz" .
```

### 2. Construir e Iniciar os Containers

#### Windows

```powershell
.\build-container.ps1
```

#### Linux/Mac

```bash
chmod +x build-container.sh
./build-container.sh
```

### 3. Verificar Status

#### Windows

```powershell
.\check-status.ps1
```

Para visualizar logs:

```powershell
.\check-status.ps1 -logs
```

Para visualizar logs de um serviço específico:

```powershell
.\check-status.ps1 -logs -service fila-api
```

#### Linux/Mac

```bash
docker-compose ps
docker-compose logs -f [serviço]
```

### 4. Acessar o Sistema

O sistema estará disponível em:

- API: http://localhost:8000/api
- Painel Administrativo: http://localhost:8000

### 5. Parar os Containers

```bash
docker-compose down
```

## Estrutura de Diretórios no Container

Dentro do container, o código está localizado em `/var/www/html` com a seguinte estrutura:

```
/var/www/html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── vendor/
```

## Solução de Problemas

### Erro de Conexão com o Banco de Dados

Verifique se o container do banco de dados está em execução:

```bash
docker-compose ps fila-db
```

### Erro de Conexão com o Redis

Verifique se o container do Redis está em execução:

```bash
docker-compose ps fila-redis
```

### Logs de Erro

Para visualizar os logs de erro:

```bash
docker-compose logs -f fila-api
```

### Reiniciar um Serviço

```bash
docker-compose restart [serviço]
```

## Manutenção

### Atualizar o Código

Para atualizar o código no container, é necessário reconstruir a imagem:

1. Faça as alterações necessárias nos arquivos locais
2. Execute o script de construção novamente

### Backup do Banco de Dados

```bash
docker exec fila-db mysqldump -u root -proot fila_api > backup_$(date +%Y%m%d).sql
```

## Notas Importantes

- Todas as alterações feitas diretamente no container serão perdidas quando o container for recriado
- Para alterações permanentes, modifique os arquivos locais e reconstrua a imagem
- O banco de dados e o Redis possuem volumes persistentes, então os dados serão mantidos mesmo após recriar os containers 