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