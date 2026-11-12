# syntax=docker/dockerfile:1

FROM composer:2 AS composer

FROM php:8.3-fpm-bookworm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql opcache zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV AUDIO_STORAGE_PATH=/var/lib/tonebase/audio

COPY composer.json ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader

COPY . .
COPY .env.docker.example .env

RUN composer dump-autoload --no-dev --classmap-authoritative \
    && mkdir -p var/cache var/log /var/lib/tonebase/audio \
    && chown -R www-data:www-data var /var/lib/tonebase

COPY docker/php/conf.d/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
