#!/bin/sh

# Script para otimizar assets automaticamente
echo "🚀 Otimizando assets para performance máxima..."

# Função para comprimir arquivos
compress_file() {
    local file="$1"
    if [ -f "$file" ]; then
        echo "📦 Comprimindo: $file"
        gzip -9 -c "$file" > "$file.gz"
        echo "✅ Arquivo comprimido: $file.gz"
    fi
}

# Função para otimizar diretório de assets
optimize_assets_dir() {
    local dir="$1"
    if [ -d "$dir" ]; then
        echo "🔍 Otimizando diretório: $dir"
        
        # Comprimir CSS
        find "$dir" -name "*.css" -type f | while read file; do
            compress_file "$file"
        done
        
        # Comprimir JS
        find "$dir" -name "*.js" -type f | while read file; do
            compress_file "$file"
        done
        
        # Comprimir HTML
        find "$dir" -name "*.html" -type f | while read file; do
            compress_file "$file"
        done
        
        echo "✅ Diretório otimizado: $dir"
    fi
}

# Aguardar o Nginx iniciar
echo "⏳ Aguardando Nginx iniciar..."
sleep 5

# Otimizar diretório público
if [ -d "/var/www/html/public" ]; then
    echo "🎯 Otimizando diretório público..."
    optimize_assets_dir "/var/www/html/public"
fi

# Otimizar diretório de build
if [ -d "/var/www/html/public/build" ]; then
    echo "🎯 Otimizando diretório de build..."
    optimize_assets_dir "/var/www/html/public/build"
fi

# Otimizar diretório de assets
if [ -d "/var/www/html/public/build/assets" ]; then
    echo "🎯 Otimizando assets..."
    optimize_assets_dir "/var/www/html/public/build/assets"
fi

echo "🎉 Otimização concluída! Site voando alto! 🚀"

# Manter o script rodando para monitorar mudanças
while true; do
    sleep 300  # Verificar a cada 5 minutos
    echo "🔄 Verificando novos assets para otimizar..."
    
    # Otimizar novos arquivos se houver
    if [ -d "/var/www/html/public/build/assets" ]; then
        optimize_assets_dir "/var/www/html/public/build/assets"
    fi
done
