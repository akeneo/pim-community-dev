#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$@

fail=0
for TESTFILE in $TESTFILES; do
    echo $TESTFILE
    docker-compose exec -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/behat_$(uuidgen) --format pretty --out std -p legacy $TESTFILE
    fail=$(($fail + $?))
done

return $fail
