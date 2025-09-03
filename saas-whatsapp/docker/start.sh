#!/bin/bash

# Aguardar o MySQL estar disponível
echo "Aguardando MySQL..."
while ! nc -z saas-db 3306; do
  sleep 1
done
echo "MySQL disponível!"

cd /var/www/html

# Instalar dependências
composer install --no-interaction --optimize-autoloader

# Verificar se o arquivo .env existe, caso contrário, criar a partir do .env.example
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Aplicar migrações
php artisan migrate --force

# Limpar e otimizar
php artisan optimize:clear
php artisan optimize

# Permissões para diretórios de armazenamento
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Iniciar o supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf 