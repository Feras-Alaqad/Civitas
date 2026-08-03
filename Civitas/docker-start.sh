#!/bin/sh
set -e

php artisan optimize:clear

if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=..+' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

php artisan migrate --force --graceful --no-interaction || true

php artisan optimize:clear || true

php artisan storage:link || true

exec frankenphp php-server -r public
