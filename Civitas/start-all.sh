#!/bin/sh
set -e

cd "$(dirname "$0")"

export APP_ENV=production
export APP_DEBUG=false

[ -f .env ] || touch .env

if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=..+' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

php artisan optimize:clear || true

if [ -n "$MYSQL_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$MYSQL_URL"
fi
[ -z "$DB_CONNECTION" ] && export DB_CONNECTION=mysql

php artisan migrate --force --graceful --no-interaction || true
php artisan storage:link || true

# Meilisearch (internal only, only if a local binary is present)
if command -v meilisearch >/dev/null 2>&1; then
    echo "Starting Meilisearch (internal)..."
    nohup meilisearch \
        --master-key="${MEILISEARCH_KEY:-your-secret-master-key-here}" \
        --http-addr="127.0.0.1:7700" \
        --env="production" \
        > /tmp/meilisearch.log 2>&1 &
    sleep 2
fi

echo "Starting Laravel Queue Worker..."
nohup php artisan queue:work --sleep=1 --tries=3 --memory=512 > /tmp/queue.log 2>&1 &

echo "Starting Laravel Scheduler..."
nohup php artisan schedule:work > /tmp/schedule.log 2>&1 &

echo "Starting Web Server on :${PORT:-80}..."
if command -v frankenphp >/dev/null 2>&1; then
    exec frankenphp php-server -r public --listen ":${PORT:-80}"
else
    exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
fi
