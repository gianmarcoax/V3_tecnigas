# ============================================================
# Dockerfile — Dashboard Tecnigas (Laravel 11 / PHP 8.2-FPM)
# Build multi-stage:
#   Stage 1 (node-builder) → compila assets Vite/TailwindCSS
#   Stage 2 (composer-builder) → instala dependencias PHP
#   Stage 3 (production) → imagen final liviana PHP-FPM
# ============================================================

# ────────────────────────────────────────────────────────────
# STAGE 1 — Node: compila assets Vite + TailwindCSS
# ────────────────────────────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copiar solo lo necesario para instalar deps de Node
COPY package.json package-lock.json ./
RUN npm ci

# Copiar fuentes de assets
COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./

# Copiar vistas Blade (Tailwind las necesita para purge)
COPY resources/views/ ./resources/views/

# Generar build de producción en public/build/
RUN npm run build


# ────────────────────────────────────────────────────────────
# STAGE 2 — Composer: instala dependencias PHP sin dev
# ────────────────────────────────────────────────────────────
FROM composer:2.7 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


# ────────────────────────────────────────────────────────────
# STAGE 3 — Producción: imagen final PHP-FPM
# ────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS production

# Labels de metadata
LABEL maintainer="Tecnigas Dev Team"
LABEL description="Dashboard Tecnigas — Laravel 11 / PHP 8.2-FPM"

# ── Instalar extensiones PHP requeridas ──────────────────────
# pgsql + pdo_pgsql → PostgreSQL
# zip → ext-zip (BarTender .xlsx)
# curl, dom, xml, mbstring → OdooService XML-RPC
# intl → formateo numérico Laravel
# opcache → rendimiento producción
RUN apk add --no-cache \
        libpq-dev \
        libzip-dev \
        libxml2-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        dom \
        xml \
        mbstring \
        intl \
        opcache \
        pcntl \
        bcmath

# ── Configurar OPcache para producción ───────────────────────
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini

# ── Copiar configuración PHP personalizada ────────────────────
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# ── Crear usuario no-root para seguridad ─────────────────────
RUN addgroup -g 1000 -S laravel \
    && adduser -u 1000 -S laravel -G laravel

# ── Directorio de trabajo ─────────────────────────────────────
WORKDIR /var/www/html

# ── Copiar dependencias de los stages anteriores ─────────────
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build

# ── Copiar código fuente (excluye node_modules, vendor, .env)
COPY --chown=laravel:laravel . .

# ── Reemplazar vendor con el del builder (sin dev deps) ──────
COPY --from=composer-builder --chown=laravel:laravel /app/vendor ./vendor

# ── Crear estructura de storage necesaria ────────────────────
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R laravel:laravel \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

# ── Optimizar autoloader de Composer ─────────────────────────

# ── Cambiar a usuario no-root ─────────────────────────────────
USER laravel

# PHP-FPM escucha en puerto 9000
EXPOSE 9000

CMD ["php-fpm"]
