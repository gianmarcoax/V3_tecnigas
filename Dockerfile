
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources/ ./resources/

COPY vite.config.js tailwind.config.js postcss.config.js ./

RUN npm run build

FROM composer:2.7 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

FROM php:8.2-fpm-alpine AS production

RUN apk add --no-cache libpq-dev libzip-dev libxml2-dev icu-dev oniguruma-dev \

    && docker-php-ext-install pdo pdo_pgsql pgsql zip dom xml mbstring intl opcache pcntl bcmath

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \

    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \

    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \

    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

RUN addgroup -g 1000 -S laravel && adduser -u 1000 -S laravel -G laravel

WORKDIR /var/www/html

COPY --from=composer-builder /app/vendor ./vendor

COPY --from=node-builder /app/public/build ./public/build

COPY --chown=laravel:laravel . .

COPY --from=composer-builder --chown=laravel:laravel /app/vendor ./vendor

RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \

    && chown -R laravel:laravel storage bootstrap/cache \

    && chmod -R 775 storage bootstrap/cache


USER laravel

EXPOSE 9000

CMD ["php-fpm"]

