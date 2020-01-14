#!/bin/bash

set -eo pipefail

TEST_SUITE=$1

ID=$(uuidgen)

TESTS=docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TEST_SUITE | circleci tests split --split-by=timings > var/tests/behat/behat_tests_$ID.scenarios
docker-compose exec -u www-data -T fpm ./vendor/bin/behat --strict --format pim --out var/tests/behat/behat_tests_$ID.results.xml --format pretty --out std --colors -p legacy -s $TEST_SUITE $TEST_FILE
