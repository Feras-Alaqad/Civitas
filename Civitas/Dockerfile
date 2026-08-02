FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM dunglas/frankenphp:php8.4-bookworm

RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    bcmath \
    opcache \
    redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .
COPY --from=frontend /app/public/build /app/public/build

RUN composer install \
    --optimize-autoloader \
    --no-dev \
    --no-interaction

EXPOSE 80

CMD ["sh", "-c", "php artisan migrate --force --no-interaction && php artisan storage:link && frankenphp php-server -r public"]
