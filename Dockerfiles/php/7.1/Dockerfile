FROM composer

RUN apk --no-cache add zlib-dev libmcrypt-dev icu-dev libjpeg-turbo-dev libpng-dev curl-dev libxml2-dev busybox-suid

ENV LIBRARY_PATH=/lib:/usr/lib

RUN docker-php-ext-install zip intl bcmath gd curl mbstring xml pdo_mysql mcrypt

RUN adduser -D php
