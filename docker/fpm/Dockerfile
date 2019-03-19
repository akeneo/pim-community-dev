FROM akeneo/fpm:php-7.2

# Create volumes target
RUN mkdir -p /data/archive && chmod 777 /data/archive && \
    mkdir -p /data/catalog && chmod 777 /data/catalog && \
    mkdir -p /data/tmp && chmod 777 /data/tmp && \
    mkdir -p /srv/pim/var/cache && chmod 777 /srv/pim/var/cache && \
    mkdir -p /srv/pim/var/logs && chmod 777 /srv/pim/var/logs && \
    mkdir -p /srv/pim/var/tests && chmod 777 /srv/pim/var/tests

# Expose volumes
VOLUME /data/archive
VOLUME /data/catalog
VOLUME /data/tmp
VOLUME /srv/pim/var/cache
VOLUME /srv/pim/var/logs
VOLUME /srv/pim/var/tests
VOLUME /srv/pim
