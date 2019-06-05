#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$(dirname $0)
PIM_PATH="$SCRIPT_DIR/../../.."

echo "Install blackfire"

cd $PIM_PATH
docker-compose exec -T fpm bash -c "wget -q -O - https://packages.blackfire.io/gpg.key | sudo apt-key add -"
docker-compose exec -T fpm bash -c 'echo "deb http://packages.blackfire.io/debian any main" | sudo tee /etc/apt/sources.list.d/blackfire.list'
docker-compose exec -T fpm sudo apt-get update
docker-compose exec -T fpm sudo apt-get install -y --allow-unauthenticated blackfire-agent
docker-compose exec -T fpm bash -c "printf '$BLACKFIRE_SERVER_ID\n$BLACKFIRE_SERVER_TOKEN\n' | sudo blackfire-agent --register"
docker-compose exec -T fpm sudo /etc/init.d/blackfire-agent restart
docker-compose exec -T fpm bash -c "printf '$BLACKFIRE_CLIENT_ID\n$BLACKFIRE_CLIENT_TOKEN\n' | blackfire config -h"
docker-compose exec -T fpm sudo apt-get install -y --allow-unauthenticated blackfire-php
docker-compose restart fpm
docker-compose exec -T fpm sudo /etc/init.d/blackfire-agent restart
docker-compose exec fpm php -d memory_limit=3G /usr/local/bin/composer require blackfire/php-sdk
