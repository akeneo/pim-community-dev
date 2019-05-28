#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$(dirname $0)
$SCRIPT_DIR/../tests/back/Performance/install_blackfire.sh
$SCRIPT_DIR/../tests/back/Performance/load_reference_catalog.sh

docker-compose exec -T fpm ./vendor/bin/phpunit -c app/phpunit.xml.dist --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml --testsuite PIM_Performance_Test
