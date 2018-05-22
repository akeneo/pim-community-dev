FROM php:7.1-apache

ENV COMPOSER_CACHE_DIR=/tmp/composer/cache
ENV YARN_CACHE_FOLDER=/tmp/yarn
ENV PIM_DATABASE_HOST=127.0.0.1
ENV PIM_INDEX_HOSTS=elastic:changeme@127.0.0.1:9200

RUN apt-get update && apt-get install -y apt-transport-https \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && curl -sS https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add - \
  && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
  && echo "deb https://deb.nodesource.com/node_8.x jessie main" > /etc/apt/sources.list.d/nodesource.list \
  && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
  && apt-get update && apt-get install -y --no-install-recommends \
    git \
    imagemagick \
    libcurl4-openssl-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libmagickwand-dev \
    libmcrypt-dev \
    libpng-dev \
    libxml2-dev \
    netcat \
    nodejs \
    perceptualdiff \
    yarn \
    zlib1g-dev \
    zlib1g-dev \
  && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j$(nproc) \
      bcmath \
      curl \
      exif \
      gd \
      intl \
      mbstring \
      mcrypt \
      opcache \
      pdo_mysql \
      soap \
      xml \
      zip \
    && pecl install apcu && docker-php-ext-enable apcu \
    && pecl install imagick && docker-php-ext-enable imagick \
    && rm -rf /tmp/pear \
    && a2enmod rewrite \
    && a2enmod proxy \
    && a2enmod proxy_fcgi

COPY --from=gcr.io/akeneo-ci/github-akeneo-ci-dependencies-warmer:master /tmp /tmp
COPY . .
COPY .ci/php.ini /usr/local/etc/php/
COPY .ci/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN composer install --no-ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-suggest \
    && bin/console pim:installer:assets --env=prod \
    && bin/console pim:installer:dump-require-paths --env=prod \
    && yarn install \
    && yarn run webpack \
    && rm -rf var/cache/* var/logs/* \
    && sed -i "s/icecat_demo_dev/minimal/g" app/config/pim_parameters.yml \
    && mkdir -p /tmp/pim app/file_storage app/uploads app/archive \
    && chown -R www-data:www-data var web app /tmp/pim \
    && chmod 777 -R /tmp/pim app/file_storage app/uploads app/archive features/Context/fixtures/ var/cache/ var/logs/
