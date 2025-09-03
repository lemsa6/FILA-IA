FROM nginx:alpine

# Instalar ferramentas de compressão e otimização
RUN apk add --no-cache \
    gzip \
    brotli \
    curl \
    && rm -rf /var/cache/apk/*

# Copiar nossa configuração personalizada
COPY default.conf /etc/nginx/conf.d/default.conf

# Criar diretório para logs
RUN mkdir -p /var/log/nginx

# Expor a porta 80
EXPOSE 80

# Script de inicialização para otimizar arquivos
COPY optimize-assets.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/optimize-assets.sh

# Comando padrão
CMD ["nginx", "-g", "daemon off;"]
