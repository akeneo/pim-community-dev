FROM akeneo/fpm:php-7.2

ARG COMPOSER_COMMAND=update

ENV COMPOSER_CACHE_DIR=/tmp/composer/cache
ENV COMPOSER_MEMORY_LIMIT=5G
ENV YARN_CACHE_FOLDER=/tmp/yarn
ENV PIM_DATABASE_HOST=127.0.0.1
ENV PIM_DATABASE_PORT=3306
ENV PIM_DATABASE_NAME=akeneo_pim
ENV PIM_DATABASE_USER=akeneo_pim
ENV PIM_DATABASE_PASSWORD=changeme
ENV PIM_INDEX_HOSTS=elastic:changeme@127.0.0.1:9200
ENV BEHAT_TMPDIR=/var/www/pim/var/cache/tmp

WORKDIR /var/www/pim

RUN ln -s /usr/sbin/php-fpm7.2 /usr/local/bin/php-fpm \
    && sed -i "s/user = docker/user = www-data/" /etc/php/7.2/fpm/pool.d/www.conf \
    && sed -i "s/group = docker/group = www-data/" /etc/php/7.2/fpm/pool.d/www.conf \
    && apt-get update \
    && curl -sS https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add - \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://deb.nodesource.com/node_8.x stretch main" > /etc/apt/sources.list.d/nodesource.list \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && apt-get update && apt-get install -y --no-install-recommends apache2 netcat nodejs yarn \
    && a2enmod rewrite \
    && a2enmod proxy \
    && a2enmod proxy_fcgi \
    ## Pupeter & Cucumber JS
    && apt-get install -yq libgconf-2-4 \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list' \
    && apt-get update \
    && apt-get install -y google-chrome-unstable fonts-ipafont-gothic fonts-wqy-zenhei fonts-thai-tlwg fonts-kacst ttf-freefont --no-install-recommends \
    && apt-get install -yq gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 \
        libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 \
        libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 \
        libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
        fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils

COPY composer.json composer.lock package.json ./

RUN composer  $COMPOSER_COMMAND --no-ansi --no-autoloader --no-scripts --no-interaction --no-progress --prefer-dist --no-suggest

COPY . .
COPY .ci/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN composer $COMPOSER_COMMAND --no-ansi --no-interaction --no-progress --prefer-dist --no-suggest \
    && bin/console pim:installer:assets --env=prod \
    && bin/console pim:installer:dump-extensions --env=prod \
    && bin/console pim:installer:dump-require-paths --env=prod \
    && yarn install \
    && yarn run webpack \
    && printf "    pim_job_product_batch_size: 50\n" >> app/config/parameters.yml \
    && cp app/config/parameters.yml app/config/parameters_test.yml \
    && chown -R www-data:www-data var web \
    && chmod 777 -R tests/legacy/features/Context/fixtures/ var web
