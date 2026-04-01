# Build assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Install PHP dependencies
FROM composer:2 AS vendor-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=ext-pcntl

# Runtime
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    curl \
    libpq-dev \
    sqlite-dev \
    oniguruma-dev \
    zip \
    unzip \
    postgresql-client \
    fcgi

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    bcmath \
    pcntl \
    opcache

WORKDIR /app

COPY . .
COPY --from=vendor-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build
RUN test -f /app/public/build/manifest.json


RUN mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/views /app/storage/framework/sessions /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R ug+rwx /app/storage /app/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
