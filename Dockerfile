FROM php:7.1-apache AS php71
RUN apt-get update && apt-get install -y \
    git \
    libpq-dev \
    openjdk-17-jdk \        
    && docker-php-ext-install pdo pdo_pgsql
WORKDIR /var/www/html
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install xdebug-2.9.6 && docker-php-ext-enable xdebug

FROM php:8.2-apache AS php82
RUN apt-get update && apt-get install -y \
    git \
    libpq-dev \    
    openjdk-17-jdk \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql
WORKDIR /var/www/html 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN git config --global --add safe.directory /var/www/html

FROM php:8.4.4-zts-alpine3.21 AS php84
RUN apk add --no-cache --update \
    autoconf \
    build-base \
    git \
    linux-headers \    
    openjdk17-jdk \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && git config --global --add safe.directory /var/www/html