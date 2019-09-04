#!/usr/bin/env bash

set -eu

# In order to execute this script locally, you will need to set the BLACKFIRE_xxx environment variables in your host, e.g:
# export BLACKFIRE_SERVER_ID=my_blackfire_server_id
# export BLACKFIRE_SERVER_TOKEN=my_blackfire_server_token
# export BLACKFIRE_CLIENT_ID=my_blackfire_client_id
# export BLACKFIRE_CLIENT_TOKEN=my_blackfire_client_token

SCRIPT_DIR=$(dirname $0)
$SCRIPT_DIR/../tests/back/Performance/install_blackfire.sh
$SCRIPT_DIR/../tests/back/Performance/load_reference_catalog.sh

# wait ES to index everything before starting anything, which can alter the result
sleep 10

docker-compose exec -T -e BLACKFIRE_SERVER_ID -e BLACKFIRE_SERVER_TOKEN -e BLACKFIRE_CLIENT_ID -e BLACKFIRE_CLIENT_TOKEN \
 -e APP_ENV=behat -u www-data fpm ./vendor/bin/phpunit -c phpunit.xml.dist --log-junit var/tests/phpunit/phpunit_$(uuidgen).xml --testsuite PIM_Performance_Test
