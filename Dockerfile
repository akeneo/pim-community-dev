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
    PHP_CONF_DISPLAY_ERRORS=0 \
    PHP_CONF_DISPLAY_STARTUP_ERRORS=0

RUN echo 'APT::Install-Recommends "0" ; APT::Install-Suggests "0" ;' > /etc/apt/apt.conf.d/01-no-recommended && \
    echo 'path-exclude=/usr/share/man/*' > /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    echo 'path-exclude=/usr/share/doc/*' >> /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install \
        apt-transport-https \
        aspell \
        aspell-en aspell-es aspell-de aspell-fr aspell-it aspell-sv aspell-da aspell-nl aspell-no aspell-pt-br \
        ca-certificates \
        curl \
        wget \
        ghostscript \
        gpg \
        gpg-agent \
        libcurl4-openssl-dev \
        libssl-dev && \
     wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg &&\
        sh -c 'echo "deb https://packages.sury.org/php/ buster main" > /etc/apt/sources.list.d/php.list' &&\
    apt-get update && \
    apt-get clean && \
    apt-get --yes autoremove --purge && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    apt-get update && \
    apt-get --yes install \
        ca-certificates \
        imagemagick \
        libmagickcore-6.q16-2-extra \
        php8.0-fpm \
        php8.0-cli \
        php8.0-intl \
        php8.0-opcache \
        php8.0-memcached \
        php8.0-mysql \
        php8.0-zip \
        php8.0-xml \
        php8.0-curl \
        php8.0-mbstring \
        php8.0-bcmath \
        php8.0-imagick \
        php8.0-gd \
        php8.0-apcu \
        php8.0-exif \
        php8.0-grpc \
        php8.0-protobuf && \
    apt-get install --yes --quiet libpcre2-8-0 && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/sbin/php-fpm8.0 /usr/local/sbin/php-fpm && \
    usermod --uid 1000 www-data && groupmod --gid 1000 www-data && \
    mkdir /srv/pim && \
    usermod -d /srv/pim www-data && \
    mkdir -p /run/php

# https://akeneo.atlassian.net/browse/PIM-9350
RUN sed -i '/<!-- <policy domain="module" rights="none" pattern="{PS,PDF,XPS}" \/> -->/c\  <policy domain="module" rights="read|write" pattern="{PS,PDF,XPS}" \/>' /etc/ImageMagick-6/policy.xml
RUN sed -i '/<policy domain="coder" rights="none" pattern="PDF" \/>/c\  <policy domain="coder" rights="read|write" pattern="PDF" \/>' /etc/ImageMagick-6/policy.xml

COPY docker/php.ini /etc/php/8.0/cli/conf.d/99-akeneo.ini
COPY docker/php.ini /etc/php/8.0/fpm/conf.d/99-akeneo.ini

#
# Image used for development
#
FROM base AS dev

ENV PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=1

RUN apt-get update && \
    apt-get --yes install gnupg &&\
    sh -c 'wget -q -O - https://packages.blackfire.io/gpg.key |APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn apt-key add -' &&\
    sh -c 'echo "deb http://packages.blackfire.io/debian any main" >  /etc/apt/sources.list.d/blackfire.list' &&\
    apt-get update && \
    apt-get --yes install \
        blackfire \
        blackfire-php \
        curl \
        default-mysql-client \
        git \
        perceptualdiff \
        php8.0-xdebug \
        procps \
        unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY docker/build/xdebug.ini /etc/php/8.0/cli/conf.d/99-akeneo-xdebug.ini
COPY docker/build/xdebug.ini /etc/php/8.0/fpm/conf.d/99-akeneo-xdebug.ini
COPY docker/build/blackfire.ini /etc/php/8.0/cli/conf.d/99-akeneo-blackfire.ini
COPY docker/build/blackfire.ini /etc/php/8.0/fpm/conf.d/99-akeneo-blackfire.ini

# TODO RAB-1083: remove fixed version when https://github.com/composer/composer/issues/11073 is resolved
COPY --from=composer:2.4.1 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

VOLUME /srv/pim

#
# Intermediate image to install
# the app dependencies for production
#
FROM dev AS builder

ARG COMPOSER_AUTH

# Install NodeJS 14 and Yarn
RUN sh -c 'wget -q -O - https://deb.nodesource.com/gpgkey/nodesource.gpg.key | APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn apt-key add -' && \
    sh -c 'echo "deb https://deb.nodesource.com/node_14.x buster main" > /etc/apt/sources.list.d/nodesource.list' && \
    sh -c 'echo "deb-src https://deb.nodesource.com/node_14.x buster main" >> /etc/apt/sources.list.d/nodesource.list' && \
    sh -c 'wget -q -O - https://dl.yarnpkg.com/debian/pubkey.gpg | APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn apt-key add -' && \
    sh -c 'echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list' && \
    apt-get update && \
    apt-get install -y nodejs yarn \
    && apt-get clean && apt-get -y -q autoremove --purge \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /srv/pim/

COPY bin bin
COPY config config
COPY public public
COPY frontend frontend
COPY src src
COPY components components
COPY grth grth
COPY upgrades upgrades
COPY composer.json package.json yarn.lock .env tsconfig.json *.js .

ENV APP_ENV=prod
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1
ENV GOOGLE_CLOUD_PROJECT="akecld_google_cloud_project_dummy"
ENV SRNT_GOOGLE_APPLICATION_CREDENTIALS="/srv/pim/config/fake_credentials_gcp.json"
ENV SRNT_GOOGLE_BUCKET_NAME="srnt_google_bucket_dummy"

RUN mkdir var && \
    composer config repositories.grth '{"type": "path", "url": "grth/", "options": {"symlink": false }}' && \
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
    yarnpkg run update-extensions && \
    yarnpkg run less && \
    yarnpkg run packages:build && \
    EDITION=cloud yarnpkg run webpack && \
    find . -type d -name node_modules | xargs rm -rf && \
    rm -rf public/test_dist && \
    (test -d vendor/akeneo/pim-community-dev/upgrades/schema/ && cp vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema/ || true) && \
    (test -d grth/upgrades/schema/ && cp grth/upgrades/schema/* upgrades/schema/ || true) && \
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
COPY --from=builder --chown=www-data:www-data /srv/pim/grth/src/Akeneo grth/src/Akeneo
COPY --from=builder --chown=www-data:www-data /srv/pim/components components
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
