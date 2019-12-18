FROM akeneo/pim-php-base:4.0

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

# Make XDEBUG activable at container start
COPY docker/build/docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

ENTRYPOINT ["/usr/local/bin/docker-php-entrypoint"]

VOLUME /srv/pim
