#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$(dirname $0)
PIM_PATH="$SCRIPT_DIR/../../.."
DOCKER_COMPOSE_EXEC='docker-compose exec -T -e APP_ENV=behat'

echo "Install blackfire"

cd $PIM_PATH
$DOCKER_COMPOSE_EXEC fpm apt-get update
$DOCKER_COMPOSE_EXEC fpm apt-get install -y gnupg2 wget
$DOCKER_COMPOSE_EXEC fpm bash -c "wget -q -O - https://packages.blackfire.io/gpg.key | apt-key add -"
$DOCKER_COMPOSE_EXEC fpm bash -c 'echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list'
$DOCKER_COMPOSE_EXEC fpm apt-get update
$DOCKER_COMPOSE_EXEC fpm apt-get install -y --allow-unauthenticated blackfire-agent
$DOCKER_COMPOSE_EXEC fpm bash -c 'chown www-data /var/www'
$DOCKER_COMPOSE_EXEC fpm bash -c "printf '$BLACKFIRE_SERVER_ID\n$BLACKFIRE_SERVER_TOKEN\n' | blackfire-agent --register"
$DOCKER_COMPOSE_EXEC fpm /etc/init.d/blackfire-agent restart
$DOCKER_COMPOSE_EXEC -u www-data fpm bash -c "printf '$BLACKFIRE_CLIENT_ID\n$BLACKFIRE_CLIENT_TOKEN\n' | blackfire config -h"
$DOCKER_COMPOSE_EXEC fpm apt-get install -y --allow-unauthenticated blackfire-php
docker-compose restart fpm
$DOCKER_COMPOSE_EXEC fpm bash -c "/etc/init.d/blackfire-agent restart"
