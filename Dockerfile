FROM akeneo/apache-php:php-5.6

USER root

RUN apt-get update

RUN apt-get install php5-dev --yes

RUN apt-get install unzip

RUN cd /home/docker/ && \
    wget https://github.com/BitOne/php-meminfo/archive/master.zip && \
    unzip master.zip && \
    cd php-meminfo-master/extension/php5 && \
    phpize && \
    ./configure --enable-meminfo && \
    make && \
    make install

RUN cd /home/docker/php-meminfo-master/analyzer && \
    composer update

RUN echo "extension=meminfo.so" >> /etc/php5/apache2/php.ini

RUN echo "extension=meminfo.so" >> /etc/php5/cli/php.ini

USER docker

