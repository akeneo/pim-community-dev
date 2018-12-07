FROM php:7.1.16-fpm

ENV COMPOSER_CACHE_DIR=/tmp/composer/cache
ENV COMPOSER_MEMORY_LIMIT=5G
ENV YARN_CACHE_FOLDER=/tmp/yarn
ENV PIM_DATABASE_HOST=127.0.0.1
ENV PIM_INDEX_HOSTS=elastic:changeme@127.0.0.1:9200
ENV BEHAT_TMPDIR=/var/www/pim/var/cache/tmp

WORKDIR /var/www/pim

RUN apt-get update && apt-get install -y apt-transport-https \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && curl -sS https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add - \
  && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
  && echo "deb https://deb.nodesource.com/node_8.x jessie main" > /etc/apt/sources.list.d/nodesource.list \
  && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
  && apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
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
    mysql-client \
  && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j$(nproc) \
      bcmath \
      curl \
      exif \
      gd \
      intl \
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

COPY --from=eu.gcr.io/akeneo-ci/github-akeneo-ci-dependencies-warmer:master /tmp /tmp
COPY . .
COPY .ci/php.ini /usr/local/etc/php/
COPY .ci/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN composer config --unset extra.branch-alias --working-dir=packages/pim-community-dev \
    && composer config repositories.pim-community-dev '{"type": "path", "url": "packages/pim-community-dev", "options": {"symlink": false}}'  \
    && composer require --no-update "akeneo/pim-community-dev:@dev" \
    && composer install --no-ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-suggest \
    && bin/console pim:installer:assets --env=prod \
    && bin/console pim:installer:dump-require-paths --env=prod \
    && yarn install \
    && yarn run webpack \
    && printf "    pim_job_product_batch_size: 50\n" >> app/config/parameters.yml \
    && cp app/config/parameters.yml app/config/parameters_test.yml \
    && chown -R www-data:www-data var web \
    && chmod 777 -R features/Context/fixtures/ var web vendor/akeneo/pim-community-dev/features/Context/fixtures/
