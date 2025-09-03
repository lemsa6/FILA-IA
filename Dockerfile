FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    nodejs \
    npm

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Configurar timezone e PHP
RUN echo "America/Sao_Paulo" > /etc/timezone && \
    dpkg-reconfigure -f noninteractive tzdata

# Copiar configuração personalizada do PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configuração do Supervisor
COPY docker/supervisord/horizon.conf /etc/supervisor/conf.d/horizon.conf
RUN mkdir -p /var/log/supervisor

# Copiar todos os arquivos do projeto
COPY . /var/www/html

# Copiar arquivo de ambiente
COPY .env.docker /var/www/html/.env

# Instalar dependências do PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Gerar chave da aplicação
RUN php artisan key:generate

# Instalar dependências do Node e compilar assets
RUN npm install && npm run build

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 9000

# Script de inicialização
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"] 