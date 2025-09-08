FROM php:8.3-fpm AS laravel-base

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip supervisor \
    && docker-php-ext-install pdo pdo_mysql zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
