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
        php7.4-fpm \
        php7.4-cli \
        php7.4-intl \
        php7.4-opcache \
        php7.4-memcached \
        php7.4-mysql \
        php7.4-zip \
        php7.4-xml \
        php7.4-curl \
        php7.4-mbstring \
        php7.4-bcmath \
        php7.4-imagick \
        php7.4-gd \
        php7.4-apcu \
        php7.4-exif && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/sbin/php-fpm7.4 /usr/local/sbin/php-fpm && \
    usermod --uid 1000 www-data && groupmod --gid 1000 www-data && \
    mkdir /srv/pim && \
    usermod -d /srv/pim www-data && \
    mkdir -p /run/php

# https://akeneo.atlassian.net/browse/PIM-9350
RUN sed -i '/<!-- <policy domain="module" rights="none" pattern="{PS,PDF,XPS}" \/> -->/c\  <policy domain="module" rights="read|write" pattern="{PS,PDF,XPS}" \/>' /etc/ImageMagick-6/policy.xml
RUN sed -i '/<policy domain="coder" rights="none" pattern="PDF" \/>/c\  <policy domain="coder" rights="read|write" pattern="PDF" \/>' /etc/ImageMagick-6/policy.xml

# Temporary commands for grpc and protobuf until https://github.com/oerdnj/deb.sury.org/issues/1622 is not solved
#   Bonus: if the versionned compiled extensions are no longer compatible with the PHP version
#          you can compile and push to versionning newly compiled extensions thanks to this
#          helper command: ./docker/compile_grpc_and_protobuf_pecl_extensions.sh
COPY docker/build/grpc.tar.gz /tmp/grpc.tar.gz
RUN tar xzf /tmp/grpc.tar.gz -C `php -r 'echo ini_get("extension_dir");'` && rm /tmp/grpc.tar.gz
COPY docker/build/grpc.ini /etc/php/7.4/cli/conf.d/99-grpc.ini
COPY docker/build/grpc.ini /etc/php/7.4/fpm/conf.d/99-grpc.ini

COPY docker/build/protobuf.tar.gz /tmp/protobuf.tar.gz
RUN tar xzf /tmp/protobuf.tar.gz -C `php -r 'echo ini_get("extension_dir");'` && rm /tmp/protobuf.tar.gz
COPY docker/build/protobuf.ini /etc/php/7.4/cli/conf.d/99-protobuf.ini
COPY docker/build/protobuf.ini /etc/php/7.4/fpm/conf.d/99-protobuf.ini
# end of temporary commands

COPY docker/php.ini /etc/php/7.4/cli/conf.d/99-akeneo.ini
COPY docker/php.ini /etc/php/7.4/fpm/conf.d/99-akeneo.ini

#
# Temporary stage to compile grpc and protobuf extension
#

FROM base as compile-extensions

RUN apt-get update && \
    apt-get --yes install build-essential make autoconf zlib1g-dev php7.4-dev php-pear && \
    pecl install grpc protobuf && \
    strip --strip-debug /usr/lib/php/*/grpc.so && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

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
        php7.4-xdebug \
        procps \
        unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY docker/build/xdebug.ini /etc/php/7.4/cli/conf.d/99-akeneo-xdebug.ini
COPY docker/build/xdebug.ini /etc/php/7.4/fpm/conf.d/99-akeneo-xdebug.ini
COPY docker/build/blackfire.ini /etc/php/7.4/cli/conf.d/99-akeneo-blackfire.ini
COPY docker/build/blackfire.ini /etc/php/7.4/fpm/conf.d/99-akeneo-blackfire.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

VOLUME /srv/pim

#
# Intermediate image to install
# the app dependencies for production
#
FROM dev AS builder

ARG COMPOSER_AUTH

# Install NodeJS 12 and Yarn
RUN sh -c 'wget -q -O - https://deb.nodesource.com/gpgkey/nodesource.gpg.key | APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn apt-key add -' && \
    sh -c 'echo "deb https://deb.nodesource.com/node_12.x buster main" > /etc/apt/sources.list.d/nodesource.list' && \
    sh -c 'echo "deb-src https://deb.nodesource.com/node_12.x buster main" >> /etc/apt/sources.list.d/nodesource.list' && \
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
COPY upgrades upgrades
COPY front-packages front-packages
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
    yarnpkg run update-extensions && \
    yarnpkg run less && \
    yarnpkg run packages:build && \
    EDITION=cloud yarnpkg run webpack && \
    find . -type d -name node_modules | xargs rm -rf && \
    rm -rf public/test_dist && \
    (test -d vendor/akeneo/pim-community-dev/upgrades/schema/ && cp vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema/ || true) && \
    (test -d vendor/akeneo/pim-onboarder/upgrades/schema/ && cp vendor/akeneo/pim-onboarder/upgrades/schema/* upgrades/schema/ || true)

#
# Intermediate image to install BigCommerce Connector
#
FROM builder AS bigcommerceconnector

ARG COMPOSER_AUTH

WORKDIR /srv/pim

COPY tmp tmp

# Build back
WORKDIR /srv/pim/tmp/build-connector/back/

RUN php -d 'memory_limit=4G' /usr/local/bin/composer install \
        --no-scripts \
        --no-interaction \
        --no-ansi \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader

# Build front
WORKDIR /srv/pim/tmp/build-connector/front

RUN yarnpkg install --frozen-lockfile
RUN REACT_APP_API_WEB_PATH="/connectors/bigcommerce/api-web" REACT_APP_URL_BASENAME="/connectors/bigcommerce" yarnpkg run build

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

# Copy big commerce connector
COPY --from=bigcommerceconnector --chown=www-data:www-data /srv/pim/tmp/build-connector/back connectors/bigcommerce/back
COPY --from=bigcommerceconnector --chown=www-data:www-data /srv/pim/tmp/build-connector/front/build connectors/bigcommerce/front


# Prepare the application
RUN mkdir -p public/media && chown -R www-data:www-data public/media var && \
    rm -rf var/cache && su www-data -s /bin/bash -c " \
        GOOGLE_CLOUD_PROJECT=akecld_google_cloud_project_dummy \
        SRNT_GOOGLE_APPLICATION_CREDENTIALS=/srv/pim/config/fake_credentials_gcp.json \
        SRNT_GOOGLE_BUCKET_NAME=srnt_google_bucket_dummy \
        bin/console cache:warmup"

# Keep root as default user
USER root
