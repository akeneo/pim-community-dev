#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$@

for TESTFILE in $TESTFILES; do
    echo $TESTFILE
    docker-compose exec -T fpm ./vendor/bin/behat --format junit --out var/tests/behat_$(uuidgen) --format pretty --out std -p legacy $TESTFILE
done
