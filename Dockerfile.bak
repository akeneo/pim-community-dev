FROM debian:8

ARG DEBIAN_FRONTEND=noninteractive
ARG COMPOSER_CACHE_DIR=/tmp/composer/cache

COPY --from=gcr.io/akeneo-ci/github-akeneo-ci-dependencies-warmer:master /tmp /tmp

RUN apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install \
        bzip2 ca-certificates curl git imagemagick mysql-server mongodb openjdk-7-jre ssh-client \
        php5-cli php5-apcu php5-curl php5-gd php5-imagick php5-intl php5-mongo php5-mcrypt php5-mysql \
        apache2 libapache2-mod-php5 && \
    apt-get clean && \
    apt-get --yes --quiet autoremove --purge && \
    rm -rf  /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer && \
    curl -SLO https://download-installer.cdn.mozilla.net/pub/firefox/releases/47.0.1/linux-x86_64/en-US/firefox-47.0.1.tar.bz2 && \
    tar -C /opt -xjf firefox-47.0.1.tar.bz2 && \
    mv /opt/firefox /opt/firefox-47.0.1 && \
    ln -fs /opt/firefox-47.0.1/firefox /usr/bin/firefox && \
    rm firefox-47.0.1.tar.bz2 && \
    curl -SLO https://github.com/mozilla/geckodriver/releases/download/v0.10.0/geckodriver-v0.10.0-linux64.tar.gz && \
    tar -C /opt -zxf geckodriver-v0.10.0-linux64.tar.gz && \
    mv /opt/geckodriver /opt/geckodriver-0.10.0 && \
    chmod 755 /opt/geckodriver-0.10.0 && \
    ln -fs /opt/geckodriver-0.10.0 /usr/bin/geckodriver && \
    rm geckodriver-v0.10.0-linux64.tar.gz && \
    mkdir -p /opt/selenium && \
    curl -SL https://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar -o /opt/selenium/selenium-server-standalone.jar

EXPOSE 80 3306 27017 9200 4444

COPY . /var/www/pim

WORKDIR /var/www/pim

RUN cp .ci/php.ini /etc/php5/mods-available/php.ini && \
    cp .ci/vhost.conf /etc/apache2/sites-available/000-default.conf && \
    COMPOSER_PROCESS_TIMEOUT=3000 composer update --ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-suggest && \
    chmod +x .ci/bin/start-mysql && \
    .ci/bin/start-mysql && \
    mysql -e "CREATE DATABASE IF NOT EXISTS \`akeneo_pim\` ;" && \
    mysql -e "CREATE USER 'akeneo_pim'@'%' IDENTIFIED BY 'akeneo_pim';" && \
    mysql -e "GRANT ALL ON \`akeneo_pim\`.* TO 'akeneo_pim'@'%' ;" && \
    mysql -e "FLUSH PRIVILEGES;"

CMD /bin/sh -c /var/www/pim/.ci/bin/start-webserver && /var/www/pim/.ci/bin/start-selenium && sleep infinity
