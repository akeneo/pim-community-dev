FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
    imagemagick \
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

ENV COMPOSER_HOME /tmp
ENV COMPOSER_VERSION 1.5.1

RUN curl -s -f -L -o /tmp/installer.php https://raw.githubusercontent.com/composer/getcomposer.org/da290238de6d63faace0343efbdd5aa9354332c5/web/installer \
 && php -r " \
    \$signature = '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410'; \
    \$hash = hash('SHA384', file_get_contents('/tmp/installer.php')); \
    if (!hash_equals(\$signature, \$hash)) { \
        unlink('/tmp/installer.php'); \
        echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
        exit(1); \
    }" \
 && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
 && composer --ansi --version --no-interaction \
 && rm -rf /tmp/* /tmp/.htaccess
