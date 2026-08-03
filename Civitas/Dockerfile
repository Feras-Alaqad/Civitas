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
COPY docker-start.sh /usr/local/bin/docker-start.sh

RUN chmod +x /usr/local/bin/docker-start.sh \
    && composer install \
    --optimize-autoloader \
    --no-dev \
    --no-interaction

EXPOSE 80

CMD ["/usr/local/bin/docker-start.sh"]
