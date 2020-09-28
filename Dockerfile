#
# This first image will be use as a base
# for production and development images
#
FROM debian:buster-slim AS base

ENV DEBIAN_FRONTEND=noninteractive \
    PHP_CONF_DATE_TIMEZONE=UTC \
    PHP_CONF_MAX_EXECUTION_TIME=60 \
    PHP_CONF_MEMORY_LIMIT=512M \
    PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0 \
    PHP_CONF_MAX_INPUT_VARS=1000 \
    PHP_CONF_UPLOAD_LIMIT=40M \
    PHP_CONF_MAX_POST_SIZE=40M \
    XDEBUG_ENABLED=0 \
    PHP_CONF_DISPLAY_ERRORS=0 \
    PHP_CONF_DISPLAY_STARTUP_ERRORS=0


RUN echo 'APT::Install-Recommends "0" ; APT::Install-Suggests "0" ;' > /etc/apt/apt.conf.d/01-no-recommended && \
    echo 'path-exclude=/usr/share/man/*' > /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    echo 'path-exclude=/usr/share/doc/*' >> /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install \
        apt-transport-https \
        ca-certificates \
        curl \
        libcurl4-openssl-dev \
        libssl-dev \
        gpg \
        gpg-agent \
        ghostscript \
        aspell \
        aspell-en aspell-es aspell-de aspell-fr aspell-it aspell-sv aspell-da aspell-nl aspell-no aspell-pt-br && \
    apt-get clean && \
    apt-get --yes autoremove --purge && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    apt-get update && \
    apt-get --yes install imagemagick libmagickcore-6.q16-2-extra \
        php7.3-fpm \
        php7.3-cli \
        php7.3-intl \
        php7.3-opcache \
        php7.3-mysql \
        php7.3-zip \
        php7.3-xml \
        php7.3-curl \
        php7.3-mbstring \
        php7.3-bcmath \
        php7.3-imagick \
        php7.3-gd \
        php7.3-apcu \
        php7.3-exif \
        php-memcached \
        ca-certificates && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/sbin/php-fpm7.3 /usr/local/sbin/php-fpm && \
    usermod --uid 1000 www-data && groupmod --gid 1000 www-data && \
    mkdir /srv/pim && \
    usermod -d /srv/pim www-data && \
    mkdir -p /run/php

# https://akeneo.atlassian.net/browse/PIM-9350
RUN sed -i '/<!-- <policy domain="module" rights="none" pattern="{PS,PDF,XPS}" \/> -->/c\  <policy domain="module" rights="read|write" pattern="{PS,PDF,XPS}" \/>' /etc/ImageMagick-6/policy.xml
RUN sed -i '/<policy domain="coder" rights="none" pattern="PDF" \/>/c\  <policy domain="coder" rights="read|write" pattern="PDF" \/>' /etc/ImageMagick-6/policy.xml

COPY docker/php.ini /etc/php/7.3/cli/conf.d/99-akeneo.ini
COPY docker/php.ini /etc/php/7.3/fpm/conf.d/99-akeneo.ini
COPY docker/fpm.conf /etc/php/7.3/fpm/pool.d/zzz-akeneo.conf

#
# Image used for development
#
FROM base AS dev

ENV PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=1

RUN apt-get update && \
    apt-get --yes install git unzip curl \
        default-mysql-client php7.3-xdebug procps perceptualdiff && \
    phpdismod xdebug && \
    mkdir /etc/php/7.3/enable-xdebug && \
    ln -s /etc/php/7.3/mods-available/xdebug.ini /etc/php/7.3/enable-xdebug/xdebug.ini && \
    sed -i "s#listen = /run/php/php7.3-fpm.sock#listen = 9000#g" /etc/php/7.3/fpm/pool.d/www.conf && \
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

COPY --from=composer:2.0 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

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

ARG COMPOSER_AUTH

RUN apt-get update && \
    apt-get --yes install yarnpkg nodejs && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /srv/pim/

COPY bin bin
COPY config config
COPY public public
COPY frontend frontend
COPY src src
COPY upgrades upgrades
COPY composer.json package.json yarn.lock .env tsconfig.json *.js .

ENV APP_ENV=prod
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1
ENV GOOGLE_CLOUD_PROJECT="akecld_google_cloud_project_dummy"
ENV SRNT_GOOGLE_APPLICATION_CREDENTIALS="/srv/pim/config/fake_credentials_gcp.json"
ENV SRNT_GOOGLE_BUCKET_NAME="srnt_google_bucket_dummy"

RUN mkdir var && \
    php -d 'memory_limit=4G' /usr/local/bin/composer install \
        --no-scripts \
        --no-interaction \
        --no-ansi \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader && \
    composer dump-env prod && \
    bin/console pim:installer:assets --clean && \
    yarnpkg install --frozen-lockfile && \
    yarnpkg run less && \
    EDITION=cloud yarnpkg run webpack && \
    find . -type d -name node_modules | xargs rm -rf && \
    rm -rf public/test_dist && \
    (test -d vendor/akeneo/pim-community-dev/upgrades/schema/ && cp vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema/ || true) && \
    (test -d vendor/akeneo/pim-onboarder/upgrades/schema/ && cp vendor/akeneo/pim-onboarder/upgrades/schema/* upgrades/schema/ || true)

#
# Image used for production
#
FROM base AS prod

ENV APP_ENV=prod \
    PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0

WORKDIR /srv/pim/
# Copy the application with its dependencies
COPY --from=builder --chown=www-data:www-data /srv/pim/bin bin
COPY --from=builder --chown=www-data:www-data /srv/pim/config config
COPY --from=builder --chown=www-data:www-data /srv/pim/public public
COPY --from=builder --chown=www-data:www-data /srv/pim/src src
COPY --from=builder --chown=www-data:www-data /srv/pim/upgrades upgrades
COPY --from=builder --chown=www-data:www-data /srv/pim/var/cache/prod var/cache/prod
COPY --from=builder --chown=www-data:www-data /srv/pim/vendor vendor
COPY --from=builder --chown=www-data:www-data /srv/pim/.env.local.php .
COPY --from=builder --chown=www-data:www-data /srv/pim/composer.lock .

# Prepare the application
RUN mkdir -p public/media && chown -R www-data:www-data public/media var && \
    rm -rf var/cache && su www-data -s /bin/bash -c " \
        GOOGLE_CLOUD_PROJECT=akecld_google_cloud_project_dummy \
        SRNT_GOOGLE_APPLICATION_CREDENTIALS=/srv/pim/config/fake_credentials_gcp.json \
        SRNT_GOOGLE_BUCKET_NAME=srnt_google_bucket_dummy \
        bin/console cache:warmup"

# Keep root as default user
USER root
