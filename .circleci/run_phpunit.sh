#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$1

for TESTFILE in $TESTFILES; do
    docker-compose exec -T fpm ./vendor/bin/phpunit -c app --log-junit var/tests/phpunit_$(uuidgen).xml
done
