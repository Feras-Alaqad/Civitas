#!/bin/sh
set -e

php artisan optimize:clear

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction
fi

if [ -n "$MYSQL_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$MYSQL_URL"
fi

export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-$MYSQLHOST}"
export DB_PORT="${DB_PORT:-$MYSQLPORT}"
export DB_DATABASE="${DB_DATABASE:-$MYSQLDATABASE}"
export DB_USERNAME="${DB_USERNAME:-$MYSQLUSER}"
export DB_PASSWORD="${DB_PASSWORD:-$MYSQLPASSWORD}"

php artisan migrate --force --graceful --no-interaction || true

php artisan storage:link || true

exec frankenphp php-server -r public --listen ":${PORT:-80}"