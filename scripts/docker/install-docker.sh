#!/bin/bash

# Atualizar pacotes
sudo apt update

# Instalar dependências
sudo apt install -y ca-certificates curl gnupg lsb-release

# Adicionar chave GPG oficial do Docker
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Configurar repositório do Docker
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Atualizar pacotes novamente
sudo apt update

# Instalar Docker e Docker Compose
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Iniciar e habilitar o serviço Docker
sudo systemctl enable docker
sudo systemctl start docker

# Adicionar usuário ao grupo docker para não precisar de sudo
sudo usermod -aG docker $USER

# Instalar Docker Compose standalone (opcional, caso a versão do plugin não seja suficiente)
sudo curl -L "https://github.com/docker/compose/releases/download/v2.23.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verificar instalação
echo "Versão do Docker:"
docker --version

echo "Versão do Docker Compose:"
docker compose version

echo "Instalação concluída! Você pode precisar fazer logout e login novamente para usar o Docker sem sudo." 