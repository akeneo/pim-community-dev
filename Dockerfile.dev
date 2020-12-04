FROM akeneo/pim-php-base:master

ENV PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=1

RUN apt-get update && \
    apt-get --yes install gnupg &&\
    sh -c 'wget -q -O - https://packages.blackfire.io/gpg.key |APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=DontWarn apt-key add -' &&\
    sh -c 'echo "deb http://packages.blackfire.io/debian any main" >  /etc/apt/sources.list.d/blackfire.list' &&\
    apt-get update && \
    apt-get --yes install \
        blackfire-agent \
        blackfire-php \
        curl \
        default-mysql-client \
        git \
        perceptualdiff \
        php7.4-xdebug \
        procps \
        unzip &&\
    phpdismod xdebug && \
    mkdir /etc/php/7.4/enable-xdebug && \
    ln -s /etc/php/7.4/mods-available/xdebug.ini /etc/php/7.4/enable-xdebug/xdebug.ini && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY docker/build/xdebug.ini /etc/php/7.4/cli/conf.d/99-akeneo-xdebug.ini
COPY docker/build/xdebug.ini /etc/php/7.4/fpm/conf.d/99-akeneo-xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# Make XDEBUG activable at container start
COPY docker/build/docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

ENTRYPOINT ["/usr/local/bin/docker-php-entrypoint"]

VOLUME /srv/pim
