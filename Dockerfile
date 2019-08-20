#
# This first image will be use as a base
# for production and development images
#
FROM debian:stretch-slim AS base

ENV PHP_CONF_DATE_TIMEZONE=UTC \
    PHP_CONF_MAX_EXECUTION_TIME=60 \
    PHP_CONF_MEMORY_LIMIT=512M \
    PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0
    PHP_CONF_MAX_INPUT_VARS=1000\
    PHP_CONF_UPLOAD_LIMIT=40M\
    PHP_CONF_MAX_POST_SIZE=40M

COPY docker/build/sury_org_php.gpg /etc/apt/trusted.gpg.d/sury_org_php.gpg

RUN echo 'APT::Install-Recommends "0" ; APT::Install-Suggests "0" ;' > /etc/apt/apt.conf.d/01-no-recommended && \
    echo 'path-exclude=/usr/share/man/*' > /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    echo 'path-exclude=/usr/share/doc/*' >> /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    apt-get update && \
    apt-get --yes install apt-transport-https ca-certificates && \
    echo 'deb https://packages.sury.org/php/ stretch main' > /etc/apt/sources.list.d/php-packages-sury-org.list && \
    apt-get update && \
    apt-get --yes install imagemagick \
        php7.2-fpm \
        php7.2-cli \
        php7.2-intl \
        php7.2-opcache \
        php7.2-mysql \
        php7.2-zip \
        php7.2-xml \
        php7.2-gd \
        php7.2-curl \
        php7.2-mbstring \
        php7.2-bcmath \
        php7.2-imagick \
        php7.2-apcu \
        php7.2-exif \
        php-memcached && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/sbin/php-fpm7.2 /usr/local/sbin/php-fpm && \
    usermod --uid 1000 www-data && groupmod --gid 1000 www-data && \
    mkdir /srv/pim && \
    sed -i "s#listen = /run/php/php7.2-fpm.sock#listen = 9000#g" /etc/php/7.2/fpm/pool.d/www.conf && \
    mkdir -p /run/php

COPY docker/build/akeneo.ini /etc/php/7.2/cli/conf.d/99-akeneo.ini
COPY docker/build/akeneo.ini /etc/php/7.2/fpm/conf.d/99-akeneo.ini

#
# Image used for development
#
FROM base AS dev

ENV PHP_OPCACHE_VALIDATE_TIMESTAMP=1

RUN apt-get update && \
    apt-get --yes install php7.2-xdebug && \
    phpdismod xdebug && \
    mkdir /etc/php/7.2/enable-xdebug && \
    ln -s /etc/php/7.2/mods-available/xdebug.ini /etc/php/7.2/enable-xdebug/xdebug.ini && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY docker/build/xdebug.ini /etc/php/7.2/cli/conf.d/99-akeneo-xdebug.ini
COPY docker/build/xdebug.ini /etc/php/7.2/fpm/conf.d/99-akeneo-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# Make XDEBUG activable at container start
COPY docker/build/docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

ENTRYPOINT ["/usr/local/bin/docker-php-entrypoint"]

VOLUME /srv/pim

#
# Intermediate image to install
# the app dependencies for production
#
FROM dev AS builder

COPY docker/build/yarnpkg_com.gpg /etc/apt/trusted.gpg.d/yarnpkg_com.gpg
COPY docker/build/nodesource_com.gpg /etc/apt/trusted.gpg.d/nodesource_com.gpg

RUN echo "deb https://deb.nodesource.com/node_10.x stretch main" > /etc/apt/sources.list.d/nodejs.list && \
    echo "deb http://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list && \
    apt-get update && \
    apt-get --yes install yarn \
        nodejs \
        unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /srv/pim/

COPY . .

ENV APP_ENV=prod
RUN php -d 'memory_limit=3G' /usr/local/bin/composer install --optimize-autoloader --no-scripts --no-interaction --no-ansi --no-dev --prefer-dist && \
    bin/console pim:installer:assets --symlink --clean && \
    yarn install && \
    yarn run less && \
    yarn run webpack && \
    rm -rf node_modules

#
# Image used for production
#
FROM base AS prod

ENV APP_ENV=prod \
    PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0

# Copy the application with its dependencies
WORKDIR /srv/pim/
COPY --from=builder /srv/pim/ .

# Prepare the application
RUN mkdir -p web/media && chown -R www-data:www-data web/media var
USER www-data
RUN rm -rf var/cache && bin/console cache:warmup
