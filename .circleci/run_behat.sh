#!/bin/bash

set -eo pipefail

TEST_SUITE=$1

TEST_FILES=$(docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TEST_SUITE | circleci tests split --split-by=timings)
echo "TEST FILES ON THIS CONTAINER: $TEST_FILES"

fail=0
counter=1
total=$(echo $TEST_FILES | tr ' ' "\n" | wc -l)

for TEST_FILE in $TEST_FILES; do
    echo -e "\nLAUNCHING $TEST_FILE ($counter/$total):"
    output=$(basename $TEST_FILE)_$(uuidgen)

    set +e
    docker-compose exec -u www-data -T fpm ./vendor/bin/behat --strict --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TEST_SUITE $TEST_FILE

    fail=$(($fail + $?))
    counter=$(($counter + 1))
    set -eo pipefail
done

exit $fail
