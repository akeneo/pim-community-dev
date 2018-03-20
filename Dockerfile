FROM php:7.1-fpm

ENV COMPOSER_CACHE_DIR=/tmp/composer/cache
ENV YARN_CACHE_FOLDER=/tmp/yarn

ENV BUILD_PACKAGES \
  apt-transport-https \
  autoconf \
  curl \
  g++ \
  git \
  libcurl4-gnutls-dev \
  libicu-dev \
  libjpeg62-turbo-dev \
  libmagickwand-dev \
  libmcrypt-dev \
  libncurses5-dev \
  libpng-dev \
  libssl-dev \
  libxml2-dev \
  locales \
  zlib1g-dev

COPY --from=gcr.io/akeneo-ci/github-akeneo-ci-dependencies-warmer:master /tmp /tmp

RUN apt-get update && apt-get install -y apt-transport-https \
  && set -ex; \
	export GNUPGHOME="$(mktemp -d)"; \
	key='A4A9406876FCBD3C456770C88C718D3B5072E1F5'; \
	gpg --keyserver ha.pool.sks-keyservers.net --recv-keys "$key"; \
	gpg --export "$key" > /etc/apt/trusted.gpg.d/mysql.gpg; \
	rm -r "$GNUPGHOME"; \
	apt-key list > /dev/null \
  && curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add - \
  && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
  && echo "deb https://deb.nodesource.com/node_8.x jessie main" > /etc/apt/sources.list.d/nodesource.list \
  && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
  && echo "deb http://http.debian.net/debian jessie-backports main" > /etc/apt/sources.list.d/jessie-backports.list \
  && echo "deb http://repo.mysql.com/apt/debian/ jessie mysql-5.7" > /etc/apt/sources.list.d/mysql.list \
  && { \
		echo mysql-community-server mysql-community-server/data-dir select ''; \
		echo mysql-community-server mysql-community-server/root-pass password ''; \
		echo mysql-community-server mysql-community-server/re-root-pass password ''; \
		echo mysql-community-server mysql-community-server/remove-test-db select false; \
	} | debconf-set-selections \
  && apt-get update && apt-get install -y --no-install-recommends \
  $BUILD_PACKAGES \
  apache2 \
  firefox-esr \
  imagemagick \
  libicu52 \
  libmcrypt4 \
  mysql-server="5.7.21-1debian8" \
  nodejs \
  perceptualdiff \
  xauth \
  xvfb \
  yarn \
  && apt-get install -y --no-install-recommends -t jessie-backports openjdk-8-jre \
  && curl -SLO "https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.5.3.deb" \
  && dpkg -i elasticsearch-5.5.3.deb \
  && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install \
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
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && a2enmod rewrite \
  && a2enmod proxy \
  && a2enmod proxy_fcgi \
  && a2dissite 000-default \
  && curl -SLO https://download-installer.cdn.mozilla.net/pub/firefox/releases/47.0.1/linux-x86_64/en-US/firefox-47.0.1.tar.bz2 \
  && tar -C /opt -xjf firefox-47.0.1.tar.bz2 \
  && mv /opt/firefox /opt/firefox-47.0.1 \
  && ln -fs /opt/firefox-47.0.1/firefox /usr/bin/firefox \
  && curl -SLO https://github.com/mozilla/geckodriver/releases/download/v0.10.0/geckodriver-v0.10.0-linux64.tar.gz \
  && tar -C /opt -zxf geckodriver-v0.10.0-linux64.tar.gz \
  && mv /opt/geckodriver /opt/geckodriver-0.10.0 \
  && chmod 755 /opt/geckodriver-0.10.0 \
  && ln -fs /opt/geckodriver-0.10.0 /usr/bin/geckodriver \
  && mkdir -p /opt/selenium \
  && curl -SL https://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar -o /opt/selenium/selenium-server-standalone.jar \
  ## Cleanup
  && rm -rf /var/www/html/ \
  && apt-get remove --purge -y $BUILD_PACKAGES \
  && apt-get autoremove -y \
  && apt-get clean -y \
  && rm -rf /var/lib/apt/lists/*

ADD . /var/www/pim

WORKDIR /var/www/pim

RUN cp app/config/parameters_test.yml.dist app/config/parameters_test.yml \
  && cp .ci/php.ini /usr/local/etc/php/ \
  && cp .ci/vhost.conf /etc/apache2/sites-available/pim.conf \
  && sed -i "s#database_host: .*#database_host: 127.0.0.1#g" app/config/parameters_test.yml \
  && sed -i "s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g" app/config/parameters_test.yml \
  && composer update --ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --ignore-platform-reqs --no-suggest \
  && yarn install --no-progress \
  && yarn run webpack \
  && .ci/bin/start-servers \
  && rm -rf /tmp/composer /tmp/yarn /usr/local/bin/composer \
  && mysql -e "CREATE DATABASE IF NOT EXISTS \`akeneo_pim\` ;" \
  && mysql -e "CREATE USER 'akeneo_pim'@'%' IDENTIFIED BY 'akeneo_pim';" \
  && mysql -e "GRANT ALL ON \`akeneo_pim\`.* TO 'akeneo_pim'@'%' ;" \
  && mysql -e "FLUSH PRIVILEGES;" \
  && bin/console --env=test pim:install --force \
  && a2ensite pim \
  && chown -R www-data:www-data var web \
  && chmod 777 -R /tmp/pim app/file_storage app/uploads app/archive features/Context/fixtures/

EXPOSE 80 9200 4444 3306

CMD /bin/sh -c /var/www/pim/.ci/bin/start-servers && sleep infinity