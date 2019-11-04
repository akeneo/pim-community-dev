#
# This first image will be use as a base
# for production and development images
#
FROM debian:buster-slim AS base

ENV PHP_CONF_DATE_TIMEZONE=UTC \
    PHP_CONF_MAX_EXECUTION_TIME=60 \
    PHP_CONF_MEMORY_LIMIT=512M \
    PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0 \
    PHP_CONF_MAX_INPUT_VARS=1000 \
    PHP_CONF_UPLOAD_LIMIT=40M \
    PHP_CONF_MAX_POST_SIZE=40M \
    XDEBUG_ENABLED=0

RUN echo 'APT::Install-Recommends "0" ; APT::Install-Suggests "0" ;' > /etc/apt/apt.conf.d/01-no-recommended && \
    echo 'path-exclude=/usr/share/man/*' > /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    echo 'path-exclude=/usr/share/doc/*' >> /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    apt-get update && \
    apt-get --yes install imagemagick \
        php7.3-fpm \
        php7.3-cli \
        php7.3-intl \
        php7.3-opcache \
        php7.3-mysql \
        php7.3-zip \
        php7.3-xml \
        php7.3-gd \
        php7.3-curl \
        php7.3-mbstring \
        php7.3-bcmath \
        php7.3-imagick \
        php7.3-apcu \
        php7.3-exif \
        php-memcached && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/sbin/php-fpm7.3 /usr/local/sbin/php-fpm && \
    usermod --uid 1000 www-data && groupmod --gid 1000 www-data && \
    mkdir /srv/pim && \
    sed -i "s#listen = /run/php/php7.3-fpm.sock#listen = 9000#g" /etc/php/7.3/fpm/pool.d/www.conf && \
    mkdir -p /run/php

COPY docker/build/akeneo.ini /etc/php/7.3/cli/conf.d/99-akeneo.ini
COPY docker/build/akeneo.ini /etc/php/7.3/fpm/conf.d/99-akeneo.ini

#
# Image used for development
#
FROM base AS dev

ENV PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=1

RUN apt-get update && \
    apt-get --yes install git && \
    apt-get --yes install ca-certificates && \
    apt-get --yes install unzip && \
    apt-get --yes install curl && \
    apt-get --yes install default-mysql-client && \
    apt-get --yes install php7.3-xdebug && \
    apt-get --yes install procps && \
    apt-get --yes install perceptualdiff && \
    phpdismod xdebug && \
    mkdir /etc/php/7.3/enable-xdebug && \
    ln -s /etc/php/7.3/mods-available/xdebug.ini /etc/php/7.3/enable-xdebug/xdebug.ini && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") && \
    curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version && \
    mkdir -p /tmp/blackfire && \
    tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire && \
    mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so && \
    printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > /etc/php/7.3/cli/conf.d/blackfire.ini && \
    printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > /etc/php/7.3/fpm/conf.d/blackfire.ini && \
    rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz && \
    mkdir -p /tmp/blackfire && \
    curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire && \
    mv /tmp/blackfire/blackfire /usr/bin/blackfire && \
    rm -Rf /tmp/blackfire

COPY docker/build/xdebug.ini /etc/php/7.3/cli/conf.d/99-akeneo-xdebug.ini
COPY docker/build/xdebug.ini /etc/php/7.3/fpm/conf.d/99-akeneo-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

COPY docker/pcov.so /usr/lib/php/20180731/pcov.so
RUN echo "extension=pcov.so" >> /etc/php/7.3/cli/conf.d/99-akeneo.ini
RUN echo "extension=pcov.so" >> /etc/php/7.3/fpm/conf.d/99-akeneo.ini

# Make XDEBUG activable at container start
COPY docker/build/docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

ENTRYPOINT ["/usr/local/bin/docker-php-entrypoint"]

VOLUME /srv/pim

#
# Intermediate image to install
# the app dependencies for production
#
FROM dev AS builder

RUN apt-get --yes install yarnpkg \
        nodejs \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /srv/pim/

COPY . .

ENV APP_ENV=prod
RUN php -d 'memory_limit=3G' /usr/local/bin/composer install --optimize-autoloader --no-scripts --no-interaction --no-ansi --no-dev --prefer-dist && \
    bin/console pim:installer:assets --symlink --clean && \
    yarn install --frozen-lockfile && \
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
RUN mkdir -p public/media && chown -R www-data:www-data public/media var
USER www-data
RUN rm -rf var/cache && bin/console cache:warmup
