FROM php:7.1-cli as base
RUN apt-get update && apt-get install -y zip git
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

FROM base as dev
RUN pecl install xdebug-2.9.6
WORKDIR /app
COPY composer.json .
COPY composer.lock .
RUN composer install