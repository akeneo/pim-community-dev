#!/bin/bash
#
# Usage:
#   run_phpunit.sh path/to/phpunit.xml tests/scripts/find_phpunit.php PIM_Integration_Test
#

set -eo pipefail

CONFIG_DIRECTORY=$1
FIND_PHPUNIT_SCRIPT=$2
TEST_SUITES=$3

# Removed CircleCI splitting. Now running all matching tests.
TEST_FILES=$(docker-compose run --rm -T php php $FIND_PHPUNIT_SCRIPT -c $CONFIG_DIRECTORY --testsuite $TEST_SUITES)

fail=0
for TEST_FILE in $TEST_FILES; do
    echo $TEST_FILE

    set +e
    APP_ENV=test docker-compose run -T php ./vendor/bin/phpunit -c $CONFIG_DIRECTORY --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml $TEST_FILE
    TEST_RESULT=$?
    if [ $TEST_RESULT -ne 0 ]; then
        echo "Test has failed (with code $TEST_RESULT): $TEST_FILE"
    fi
    fail=$(($fail + $TEST_RESULT))
    set -eo pipefail
done

exit $fail
