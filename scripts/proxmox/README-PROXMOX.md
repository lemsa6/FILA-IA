# Instalação do FILA-IA no Proxmox

Este documento contém instruções para instalar o sistema FILA-IA em uma VM no Proxmox.

## Pré-requisitos

- VM Ubuntu 22.04 LTS ou superior
- Docker e Docker Compose instalados
- Acesso à internet
- Ollama instalado (pode estar em outra VM)

## Arquivos necessários

- `fila-ia-full.tar.gz` - Código fonte do projeto
- `fila-ia-docker.tar.gz` - Arquivos de configuração Docker
- `proxmox-setup.sh` - Script de instalação

## Passos para instalação

### 1. Preparar a VM

1. Criar uma VM no Proxmox com Ubuntu Server
2. Instalar Docker e Docker Compose:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y ca-certificates curl gnupg lsb-release
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo systemctl enable docker
sudo usermod -aG docker $USER
```

3. Fazer logout e login novamente para aplicar as permissões do grupo docker

### 2. Transferir os arquivos

Transfira os arquivos necessários para a VM:

```bash
scp fila-ia-full.tar.gz fila-ia-docker.tar.gz proxmox-setup.sh usuario@ip-da-vm:~/
```

### 3. Executar o script de instalação

```bash
ssh usuario@ip-da-vm
chmod +x proxmox-setup.sh
./proxmox-setup.sh
```

### 4. Configurar o Ollama

Se o Ollama estiver em outra VM, edite o arquivo `.env` para apontar para o endereço correto:

```bash
cd /opt/fila-ia
nano .env
```

Altere a linha `OLLAMA_API_URL` para o endereço IP correto do servidor Ollama.

### 5. Verificar a instalação

```bash
cd /opt/fila-ia
docker-compose ps
```

Todos os containers devem estar no estado "Up".

### 6. Acessar o sistema

Acesse o sistema através do navegador:

```
http://ip-da-vm:8000
```

Use as credenciais padrão:
- Email: contato@8bits.pro
- Senha: password

### 7. Configurar proxy reverso (opcional)

Para acessar o sistema através de um domínio com HTTPS:

1. Instalar Nginx e Certbot:

```bash
sudo apt install -y nginx certbot python3-certbot-nginx
```

2. Configurar o Nginx:

```bash
sudo nano /etc/nginx/sites-available/fila-ia
```

Adicione:

```
server {
    listen 80;
    server_name seu-dominio.com;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

3. Ativar o site e obter certificado SSL:

```bash
sudo ln -s /etc/nginx/sites-available/fila-ia /etc/nginx/sites-enabled/
sudo certbot --nginx -d seu-dominio.com
sudo systemctl restart nginx
```

## Manutenção

### Backup

Para fazer backup do banco de dados:

```bash
cd /opt/fila-ia
docker-compose exec fila-db mysqldump -u fila -pfila fila_api > backup_$(date +%Y%m%d).sql
```

### Logs

Para ver os logs:

```bash
cd /opt/fila-ia
docker-compose logs -f
```

### Atualização

Para atualizar o sistema:

1. Faça backup do banco de dados
2. Pare os containers: `docker-compose down`
3. Substitua os arquivos necessários
4. Inicie os containers: `docker-compose up -d`
5. Execute as migrações: `docker-compose exec fila-api php artisan migrate --force`

## Resolução de problemas

### Erro de conexão com o Ollama

Verifique se o Ollama está rodando e acessível:

```bash
curl http://endereco-do-ollama:11434/api/tags
```

Se não estiver acessível, verifique firewall e configurações de rede.

### Erro de permissão nos arquivos

```bash
cd /opt/fila-ia
chmod -R 775 storage bootstrap/cache
```

### Problemas com o Docker

```bash
docker-compose down
docker-compose up -d
``` 