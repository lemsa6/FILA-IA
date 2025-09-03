#!/bin/bash

cd /var/www/html

echo "Aguardando o banco de dados..."
until php -r "try { new PDO('mysql:host=fila-db;dbname=fila_api', 'fila', 'fila'); echo 'Conexão com o banco de dados estabelecida!'; } catch (PDOException \$e) { echo '.'; sleep(1); }"
do
  echo -n "."
  sleep 1
done

echo "Banco de dados disponível, continuando..."

# Executar migrações
php artisan migrate --force

# Limpar cache e otimizar
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar supervisor para gerenciar processos (incluindo Horizon)
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Inicia o PHP-FPM
php-fpm