FROM httpd:2.4-bullseye AS base

ENV PHP_CONF_DATE_TIMEZONE=UTC \
    PHP_CONF_MAX_EXECUTION_TIME=60 \
    PHP_CONF_MEMORY_LIMIT=512M \
    PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0 \
    PHP_CONF_MAX_INPUT_VARS=1000 \
    PHP_CONF_UPLOAD_LIMIT=40M \
    PHP_CONF_MAX_POST_SIZE=40M

RUN echo 'APT::Install-Recommends "0" ; APT::Install-Suggests "0" ;' > /etc/apt/apt.conf.d/01-no-recommended && \
    echo 'path-exclude=/usr/share/man/*' > /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    echo 'path-exclude=/usr/share/doc/*' >> /etc/dpkg/dpkg.cfg.d/path_exclusions && \
    apt-get update && \
    apt-get --yes install \
        apt-transport-https \
        ca-certificates \
        curl \
        supervisor \
        wget &&\
    wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg &&\
    sh -c 'echo "deb https://packages.sury.org/php/ bullseye main" > /etc/apt/sources.list.d/php.list' &&\
    apt-get update && \
    apt-get --yes install imagemagick \
        libmagickcore-6.q16-6-extra \
        ghostscript \
        php8.1-fpm \
        php8.1-cli \
        php8.1-intl \
        php8.1-opcache \
        php8.1-mysql \
        php8.1-zip \
        php8.1-xml \
        php8.1-gd \
        php8.1-grpc \
        php8.1-curl \
        php8.1-mbstring \
        php8.1-bcmath \
        php8.1-imagick \
        php8.1-apcu \
        php8.1-exif \
        php8.1-memcached \
        openssh-client \
        aspell \
        aspell-en aspell-es aspell-de aspell-fr && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/sbin/php-fpm8.1 /usr/local/sbin/php-fpm && \
    usermod --uid 1000 www-data && groupmod --gid 1000 www-data && \
    mkdir /srv/pim && \
    sed -i "s#listen = /run/php/php8.1-fpm.sock#listen = 9000#g" /etc/php/8.1/fpm/pool.d/www.conf && \
    mkdir -p /run/php

COPY docker/build/akeneo.ini /etc/php/8.1/cli/conf.d/99-akeneo.ini
COPY docker/build/akeneo.ini /etc/php/8.1/fpm/conf.d/99-akeneo.ini

CMD ["/usr/bin/supervisord", "-c", "docker/supervisord.conf"]

FROM base as dev

ENV PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=1
ENV COMPOSER_MEMORY_LIMIT=4G

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
        php8.1-xdebug \
        procps \
        unzip &&\
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY docker/build/xdebug.ini /etc/php/8.1/cli/conf.d/99-akeneo-xdebug.ini
COPY docker/build/xdebug.ini /etc/php/8.1/fpm/conf.d/99-akeneo-xdebug.ini
COPY docker/build/blackfire.ini /etc/php/8.1/cli/conf.d/99-akeneo-blackfire.ini
COPY docker/build/blackfire.ini /etc/php/8.1/fpm/conf.d/99-akeneo-blackfire.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer
RUN mkdir -p /var/www/.cache && chown www-data:www-data /var/www/.cache

VOLUME /srv/pim
