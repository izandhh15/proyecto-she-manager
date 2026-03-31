# Script para agregar archivos de configuración Koyeb a tu proyecto
# Ejecuta esto en PowerShell en la raíz de tu proyecto

$projectPath = "C:\Users\Izan\Documents\GitHub\Game\New folder\proyecto-she-manager"
$dockerDir = "$projectPath\docker"

Write-Host "🚀 Iniciando configuración para Koyeb..." -ForegroundColor Green
Write-Host ""

# 1. Crear carpeta docker si no existe
if (-not (Test-Path $dockerDir)) {
    Write-Host "📁 Creando carpeta docker..." -ForegroundColor Cyan
    New-Item -ItemType Directory -Path $dockerDir -Force | Out-Null
}

# 2. Crear Dockerfile en la raíz
Write-Host "📝 Creando Dockerfile..." -ForegroundColor Cyan
$dockerfile = @'
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
'@
Set-Content -Path "$projectPath\Dockerfile" -Value $dockerfile -Encoding UTF8
Write-Host "✅ Dockerfile creado" -ForegroundColor Green

# 3. Crear docker/nginx.conf
Write-Host "📝 Creando docker/nginx.conf..." -ForegroundColor Cyan
$nginxConf = @'
server {
    listen 8080;
    server_name _;
    
    root /app/public;
    index index.php index.html;

    # Logs
    access_log /dev/stdout;
    error_log /dev/stderr;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Rewrite rules para Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Archivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Denegar acceso a archivos sensibles
    location ~ /\. {
        deny all;
    }

    location ~ /config/ {
        deny all;
    }
}
'@
Set-Content -Path "$dockerDir\nginx.conf" -Value $nginxConf -Encoding UTF8
Write-Host "✅ nginx.conf creado" -ForegroundColor Green

# 4. Crear docker/start.sh
Write-Host "📝 Creando docker/start.sh..." -ForegroundColor Cyan
$startSh = @'
#!/bin/sh
set -e

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Crear base de datos si es SQLite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    if [ ! -f /app/database/database.sqlite ]; then
        touch /app/database/database.sqlite
    fi
fi

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Seed si es la primera vez
if [ "$RUN_SEED" = "true" ]; then
    echo "Poblando datos de referencia..."
    php artisan app:seed-reference-data --force 2>/dev/null || true
fi

# Limpiar y cachear
echo "Preparando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar servicios en background
echo "Iniciando servicios..."
php-fpm -D
nginx -g "daemon off;"
'@
Set-Content -Path "$dockerDir\start.sh" -Value $startSh -Encoding UTF8 -NoNewline
Write-Host "✅ start.sh creado" -ForegroundColor Green

Write-Host ""
Write-Host "✅ Todos los archivos creados exitosamente" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Ahora ejecuta estos comandos en PowerShell:" -ForegroundColor Yellow
Write-Host ""
Write-Host "cd `"$projectPath`"" -ForegroundColor White
Write-Host "git add Dockerfile docker/" -ForegroundColor White
Write-Host "git commit -m `"feat: agregar configuración para Koyeb`"" -ForegroundColor White
Write-Host "git push origin main" -ForegroundColor White
Write-Host ""
Write-Host "⏳ Espera 2-3 minutos y luego reintenta en Koyeb" -ForegroundColor Cyan
