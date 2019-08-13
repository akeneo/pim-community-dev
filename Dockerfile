ARG BASE_IMAGE

##########################################
# This first image will be use as a base #
# for production and development images  #
##########################################

FROM ${BASE_IMAGE} AS base

ENV PHP_CONF_DATE_TIMEZONE=UTC \
    PHP_CONF_MAX_EXECUTION_TIME=60 \
    PHP_CONF_MEMORY_LIMIT=512M

# Install needed PHP extensions and related libraries
RUN apk add --no-cache \
        bash \
        icu \
        jq \
        libintl \
        libzip \
        zlib \
        libxml2-dev \
        libpng-dev \
        curl-dev \
        imagemagick \
        imagemagick-libs \
        imagemagick-dev \
    && apk add --no-cache --virtual .build-deps \
        icu-dev \
        libzip-dev \
        zlib-dev \
        $PHPIZE_DEPS \
    && pecl install apcu && docker-php-ext-enable apcu \
    && docker-php-ext-install -j$(nproc) \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        zip \
        xml \
        gd \
        curl \
        mbstring \
        bcmath \
        exif \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps

COPY docker/php/akeneo.ini $PHP_INI_DIR/conf.d/akeneo.ini

###############################
# Intermediate image to build #
# development images          #
###############################

FROM base AS dev

ENV PHP_OPCACHE_VALIDATE_TIMESTAMP=1

# Install XDEBUG
RUN apk add --no-cache --virtual .build-deps \
       $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Configure XDEBUG
COPY docker/php/xdebug.ini $PHP_INI_DIR/conf.d/

# Make XDEBUG activable at container start
COPY docker/php/docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

##################################
# Image used for CLI development #
##################################

FROM dev AS cli-dev

# Configure entrypoint for CLI
RUN sed -i 's/PROCESS_TO_RUN/php/g' /usr/local/bin/docker-php-entrypoint

# Composer needs git for PIM Enterprise Dev
RUN apk add --no-cache git

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

##################################
# Image used for FPM development #
##################################

FROM dev AS fpm-dev

# Configure entrypoint for FPM
RUN sed -i 's/PROCESS_TO_RUN/php-fpm/g' /usr/local/bin/docker-php-entrypoint

# Expose volumes
VOLUME /srv/pim

#######################################
# Intermediate image to install       #
# the app dependencies for production #
#######################################

# Back dependencies
FROM cli-dev AS builder

RUN apk add yarn

WORKDIR /var/www/html

COPY . .

ENV APP_ENV=prod
RUN composer install --optimize-autoloader --no-scripts --no-interaction --no-ansi --no-dev --prefer-dist \
    && bin/console pim:installer:assets --symlink --clean \
    && yarn install \
    && yarn run less \
    && yarn run webpack

#############################
# Image used for production #
#############################

FROM base AS prod

ENV APP_ENV=prod \
    PHP_OPCACHE_VALIDATE_TIMESTAMP=0

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy the application with its dependencies
WORKDIR /var/www/html
COPY --from=builder /var/www/html .

# Prepare the application
RUN chown -R www-data:www-data /var/www/html/web/media /var/www/html/var
USER www-data
RUN rm -rf var/cache && bin/console cache:warmup
