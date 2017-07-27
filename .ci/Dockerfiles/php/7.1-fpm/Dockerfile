FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
    libcurl4-gnutls-dev \
    libicu-dev \
    libmagickwand-dev \
    libmcrypt-dev \
    libpng-dev \
    libxml2-dev \
    mysql-client \
    zlib1g-dev

RUN docker-php-ext-install \
    bcmath \
    curl \
    gd \
    intl \
    mbstring \
    mcrypt \
    opcache \
    pdo_mysql \
    soap \
    xml \
    zip

RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install imagick && docker-php-ext-enable imagick

COPY config/php.ini /usr/local/etc/php/

RUN adduser phpuser
