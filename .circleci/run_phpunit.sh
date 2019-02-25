#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$@

fail=0
for TESTFILE in $TESTFILES; do
    echo $TESTFILE
    docker-compose exec -T fpm ./vendor/bin/phpunit -c app --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml --filter $TESTFILE
    fail=$(($fail + $?))
done

return $fail
