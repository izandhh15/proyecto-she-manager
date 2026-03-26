# Build stage - Compilar assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# PHP stage - Aplicación Laravel
FROM php:8.4-fpm-alpine

# Instalar extensiones necesarias
RUN apk add --no-cache \
    nginx \
    curl \
    libpq-dev \
    sqlite-dev \
    oniguruma-dev \
    zip \
    unzip \
    git \
    postgresql-client

# Extensiones PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    ctype \
    fileinfo \
    json \
    bcmath

WORKDIR /app

# Copiar composer files
COPY composer.json composer.lock ./

# Instalar composer y dependencias PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Copiar código fuente
COPY . .

# Copiar assets compilados del stage anterior
COPY --from=node-builder /app/public/build ./public/build

# Crear estructura de directorios necesaria
RUN mkdir -p storage/logs storage/framework/cache storage/framework/views storage/framework/sessions \
    && chmod -R 755 storage bootstrap/cache

# Configurar nginx
RUN mkdir -p /etc/nginx/conf.d
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Script de entrada
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Generar APP_KEY si no existe
RUN cp .env.example .env 2>/dev/null || true

# Limpiar caché
RUN rm -rf storage/framework/cache/*

EXPOSE 8080

CMD ["/start.sh"]
