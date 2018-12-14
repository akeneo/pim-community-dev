#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$@

for TESTFILE in $TESTFILES; do
    echo $TESTFILE
    docker-compose exec fpm ./vendor/bin/phpunit -c app --log-junit var/tests/phpunit_$(uuidgen).xml $TESTFILE
done
