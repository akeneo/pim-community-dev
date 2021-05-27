#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$(dirname $0)

$SCRIPT_DIR/../tests/back/Performance/load_reference_catalog.sh

# wait ES to index everything before starting anything, which can alter the result, and warm it up with a first query
sleep 10
docker-compose run -T -u www-data --rm php curl -s http://elasticsearch:9200/akeneo_pim_product_and_product_model/_search >> /dev/null

docker-compose run -T -u www-data --rm php ./vendor/bin/phpunit -c phpunit.xml.dist --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml --testsuite PIM_Performance_Test --fail-on-skipped
