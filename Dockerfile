FROM akeneo/apache-php:php-5.6

ARG COMPOSER_CACHE_DIR=/tmp/composer/cache

COPY --from=gcr.io/akeneo-ci/github-akeneo-ci-dependencies-warmer:master /tmp /tmp

RUN apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install \
        bzip2 ca-certificates curl git imagemagick ssh-client \
        php5-cli php5-apcu php5-curl php5-gd php5-imagick php5-intl php5-mongo php5-mcrypt php5-mysql && \
    apt-get clean && \
    apt-get --yes --quiet autoremove --purge && \
    rm -rf  /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

COPY . /var/www/pim

WORKDIR /var/www/pim

RUN cp .ci/php.ini /etc/php5/mods-available/php.ini && \
    cp .ci/vhost.conf /etc/apache2/sites-available/000-default.conf && \
    COMPOSER_PROCESS_TIMEOUT=3000 composer update --ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-suggest && \
    app/console oro:requirejs:generate-config && \
    app/console assets:install && \
    chown -R www-data:www-data /var/www/pim

