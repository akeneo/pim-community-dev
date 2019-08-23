#!/bin/bash

# Usage:
#   run_phpunit.sh path/to/phpunit.xml test_file_1 test_file_2 test_file_3 test_file_4...

CONFIGDIR=$1
shift
TESTFILES=$@

fail=0
for TESTFILE in $TESTFILES; do
    # We replace dots by doubles slashes, otherwise classname is not recognised by phpunit
    TESTFILE=${TESTFILE//./\\\\}
    echo $TESTFILE
    docker-compose exec -T fpm ./vendor/bin/phpunit -c $CONFIGDIR --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml --filter $TESTFILE
    fail=$(($fail + $?))
done

exit $fail
