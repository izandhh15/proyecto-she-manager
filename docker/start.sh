#!/bin/sh
set -eu

APP_ROLE=${APP_ROLE:-web}
RUN_MIGRATIONS=${RUN_MIGRATIONS:-true}
RUN_CACHE_WARMUP=${RUN_CACHE_WARMUP:-true}
QUEUE_NAMES=${QUEUE_NAMES:-gameplay,setup,mail}

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY vacia: generando clave..."
  php artisan key:generate --force
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ ! -f /app/database/database.sqlite ]; then
  touch /app/database/database.sqlite
fi

if [ "$RUN_MIGRATIONS" = "true" ]; then
  echo "Ejecutando migraciones..."
  php artisan migrate --force
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
  echo "Poblando datos de referencia..."
  php artisan app:seed-reference-data --force || true
fi

php artisan storage:link || true

# Ensure Laravel never tries Vite dev server in production container
rm -f /app/public/hot

if [ "$RUN_CACHE_WARMUP" = "true" ]; then
  echo "Cacheando config/rutas/vistas..."
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

if [ "$APP_ROLE" = "worker" ]; then
  echo "Iniciando worker de colas: $QUEUE_NAMES"
  exec php artisan queue:work --queue="$QUEUE_NAMES" --sleep=1 --tries=1 --max-time=3600
fi

echo "Iniciando web (php-fpm + nginx)..."
# DEBUG: verify built assets on boot
ls -la /app/public
ls -la /app/public/build || true
test -f /app/public/build/manifest.json || { echo "manifest missing"; exit 1; }
php-fpm -D
exec nginx -g "daemon off;"
