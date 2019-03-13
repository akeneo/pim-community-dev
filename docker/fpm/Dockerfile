FROM akeneo/fpm:php-7.2

# Create volumes
RUN mkdir -p /srv/pim/var/cache \
    && mkdir -p /srv/pim/var/logs \
    && chmod 777 /srv/pim/var/cache \
    && chmod 777 /srv/pim/var/logs

VOLUME /srv/pim/var/cache
VOLUME /srv/pim/var/logs
VOLUME /srv/pim
