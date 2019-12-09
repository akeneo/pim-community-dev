#!/bin/bash

set -eo pipefail

TEST_SUITE=$1

TEST_FILES=$(docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TEST_SUITE | circleci tests split --split-by=timings)

fail=0
counter=1
total=$(($(echo $TEST_FILES | grep -o ' ' | wc -l) + 1))

for TEST_FILE in $TEST_FILES; do
    echo "$TEST_FILE ($counter/$total):"
    output=$(basename $TEST_FILE)_$(uuidgen)

    set +e
    docker-compose exec -u www-data -T fpm ./vendor/bin/behat --strict --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TEST_SUITE $TEST_FILE ||
    docker-compose exec -u www-data -T fpm ./vendor/bin/behat --strict --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TEST_SUITE $TEST_FILE

    fail=$(($fail + $?))
    counter=$(($counter + 1))
    set -eo pipefail
done

exit $fail
