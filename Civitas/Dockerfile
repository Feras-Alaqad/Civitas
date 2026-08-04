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
RUN cat database.csv.* > database.csv && rm -f database.csv.*
COPY --from=frontend /app/public/build /app/public/build

RUN ls -la public/build
RUN ls -la public/build/assets
COPY docker-start.sh /usr/local/bin/docker-start.sh

RUN chmod +x /usr/local/bin/docker-start.sh \
    && composer install \
    --optimize-autoloader \
    --no-dev \
    --no-interaction

RUN printf 'upload_max_filesize=600M\npost_max_size=650M\nmax_execution_time=0\nmemory_limit=512M\nmax_file_uploads=20\n' > /usr/local/etc/php/conf.d/zz-uploads.ini

EXPOSE 80

CMD ["/usr/local/bin/docker-start.sh"]
