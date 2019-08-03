#!/bin/sh

# Usage:
#   run_phpunit.sh path/to/phpunit.xml test_file_1 test_file_2 test_file_3 test_file_4...

CONFIGDIR=$1
shift
TESTFILES=$@

fail=0
for TESTFILE in $TESTFILES; do
    echo $TESTFILE
    uuid=$(uuidgen)
    docker-compose exec -T fpm ./vendor/bin/phpunit -c $CONFIGDIR --coverage-php var/coverage/${uuid}_phpunit.cov --log-junit var/tests/phpunit/phpunit_${uuid}.xml --filter $TESTFILE
    fail=$(($fail + $?))
done

return $fail
