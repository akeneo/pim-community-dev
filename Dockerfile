FROM debian:jessie-slim
MAINTAINER Akeneo Core Team <core@akeneo.com>

ENV DEBIAN_FRONTEND="noninteractive"
ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80 3306
WORKDIR /app

RUN apt-get update && \
    apt-get -yq install \
        sudo curl supervisor wget zip imagemagick
RUN echo "deb http://packages.dotdeb.org jessie all" > /etc/apt/sources.list.d/dotdeb.list
RUN wget https://www.dotdeb.org/dotdeb.gpg && apt-key add dotdeb.gpg
RUN apt-get update && \
    apt-get -yq install --no-install-recommends \
        apache2 libapache2-mod-php5 php5 php5-cli \
        php5-apcu php5-curl php5-mongo php5-intl php5-mysql \
        php5-gd php5-imagick php5-mcrypt && \
    apt-get clean && apt-get --yes --quiet autoremove --purge

RUN a2enmod rewrite && \
    php5enmod mcrypt && \
    php5dismod xdebug

RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/locale /usr/share/doc /usr/share/man

RUN sudo useradd docker --shell /bin/bash --create-home \
  && sudo usermod -a -G sudo docker \
  && echo 'ALL ALL = (ALL) NOPASSWD: ALL' >> /etc/sudoers \
  && echo 'docker:secret' | chpasswd

RUN sed -i "s/;date.timezone =/date.timezone = Etc\/UTC/" /etc/php5/cli/php.ini && \
    sed -i "s/;date.timezone =/date.timezone = Etc\/UTC/" /etc/php5/apache2/php.ini && \
    sed -i "s/memory_limit = .*/memory_limit = 2G/" /etc/php5/cli/php.ini && \
    sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php5/apache2/php.ini

RUN sed -i "s/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=docker/" /etc/apache2/envvars && \
    sed -i "s/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=docker/" /etc/apache2/envvars && \
    chown -R docker: /var/lock/apache2 && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN curl https://getcomposer.org/composer.phar > /usr/local/bin/composer && chmod +x /usr/local/bin/composer

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/akeneo.local.conf /etc/apache2/sites-available/000-default.conf

USER docker

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
