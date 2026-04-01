# Despliegue en Koyeb

Esta app ya incluye un `Dockerfile` listo para Koyeb y un entrypoint multi-rol en `docker/start.sh`.

## 1) Servicio web

Crea un servicio web en Koyeb apuntando a este repo y usando Docker.

Variables recomendadas:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<tu-dominio-koyeb>`
- `APP_KEY=<base64:...>`
- `DB_CONNECTION=pgsql`
- `DB_HOST=<host>`
- `DB_PORT=5432`
- `DB_DATABASE=<db>`
- `DB_USERNAME=<user>`
- `DB_PASSWORD=<password>`
- `QUEUE_CONNECTION=database` (o `redis` si usas Redis)
- `SESSION_DRIVER=database`
- `CACHE_STORE=database` (o `redis` si usas Redis)
- `APP_ROLE=web`
- `RUN_MIGRATIONS=true`
- `RUN_CACHE_WARMUP=true`
- `RUN_SEED=false`

Puerto:

- Koyeb debe exponer `8080`.

## 2) Servicio worker (colas)

Crea un segundo servicio en Koyeb con la **misma imagen/repo** para procesar colas.

Variables:

- Mismas variables de app y base de datos que el servicio web
- `APP_ROLE=worker`
- `RUN_MIGRATIONS=false`
- `RUN_CACHE_WARMUP=false`
- `QUEUE_NAMES=gameplay,setup,mail`

No necesita ruta pública.

## 3) Primer arranque

1. Despliega primero el servicio web.
2. Verifica que responde en `/up`.
3. Despliega el worker.
4. Crea una partida y comprueba que los jobs encolados se procesan.

## 4) Notas

- Si usas PostgreSQL en producción, evita configuraciones SQL específicas de SQLite.
- Si el `APP_KEY` no está definido, el contenedor intenta generarlo, pero en producción conviene fijarlo explícitamente como secret en Koyeb.
- Si habilitas Redis, actualiza `QUEUE_CONNECTION` y `CACHE_STORE` a `redis` en web y worker.
