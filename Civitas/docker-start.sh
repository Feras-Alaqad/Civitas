#!/bin/sh
set -e

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=..+' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

if [ -n "$MYSQL_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$MYSQL_URL"
fi

[ -z "$DB_CONNECTION" ] && export DB_CONNECTION=mysql

if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

php artisan migrate --force --graceful --no-interaction || true
php artisan storage:link || true

exec frankenphp php-server -r public
