#!/bin/sh

# Usage:
#   run_phpunit.sh path/to/phpunit.xml PIM_Integration_Test

CONFIGDIR=$1
TESTSUITES=$2

TESTFILES=$(docker-compose run -u www-data --rm -T php php .circleci/find_phpunit.php -c $CONFIGDIR --testsuite $TESTSUITES | circleci tests split --split-by=timings)

fail=0
for TESTFILE in $TESTFILES; do
    echo $TESTFILE
    docker-compose exec -u www-data -T fpm ./vendor/bin/phpunit -c $CONFIGDIR --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml $TESTFILE
    fail=$(($fail + $?))
done

return $fail
