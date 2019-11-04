#!/bin/sh

# Usage:
#   run_phpunit.sh path/to/phpunit.xml .circleci/find_phpunit.php PIM_Integration_Test

CONFIG_DIRECTORY=$1
FIND_PHPUNIT_SCRIPT=$2
TEST_SUITES=$3

TEST_FILES=$(docker-compose run -u www-data --rm -T php php $FIND_PHPUNIT_SCRIPT -c $CONFIG_DIRECTORY --testsuite $TEST_SUITES | circleci tests split --split-by=timings)

fail=0
for TEST_FILE in $TEST_FILES; do
    echo $TEST_FILE
    uuid=$(uuidgen)
    docker-compose exec -u www-data -T fpm ./vendor/bin/phpunit -c $CONFIG_DIRECTORY --coverage-php var/coverage/${uuid}_phpunit.cov --log-junit var/tests/phpunit/phpunit_$(uuid).xml $TEST_FILE
    fail=$(($fail + $?))
done

return $fail
