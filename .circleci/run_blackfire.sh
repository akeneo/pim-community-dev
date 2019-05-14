#!/usr/bin/env bash

set -eu

START=$(date +%s)
SCRIPT_DIR=$(dirname $0)
DOCKER_BRIDGE_IP=$(ip address show | grep "global docker" | cut -c10- | cut -d '/' -f1)
WORKING_DIRECTORY="$SCRIPT_DIR/../var/benchmarks"
PIM_PATH="$SCRIPT_DIR/.."
CONFIG_PATH="$PIM_PATH/tests/benchmarks/"

if [ $# -eq 0 ]; then
    REFERENCE_CATALOG_FILE="$CONFIG_PATH/product_api_catalog.yml"
else
    if [ ! -f "$CONFIG_PATH/$1" ]; then
        echo >&2 "The file does not exist"; exit 1;
    fi;
    REFERENCE_CATALOG_FILE="$CONFIG_PATH/$1"
fi;

generate_reference_catalog()
{
    echo "Generates an API user for the benchmarks in test environment"

    cd $PIM_PATH
    CREDENTIALS=$(docker-compose exec -T fpm bin/console pim:oauth-server:create-client --no-ansi -e behat generator | tr -d '\r ')
    export API_CLIENT=$(echo $CREDENTIALS | cut -d " " -f 2 | cut -d ":" -f 2)
    export API_SECRET=$(echo $CREDENTIALS | cut -d " " -f 3 | cut -d ":" -f 2)
    export API_URL="http://$DOCKER_BRIDGE_IP:8081"
    export API_USER="admin"
    export API_PASSWORD="admin"
    export API_AUTH="$(echo -n $API_CLIENT:$API_SECRET | base64 -w 0 )"

    echo "Generate the catalog"

    docker pull akeneo/data-generator:3.0

    ABSOLUTE_CATALOG_FILE=$(readlink -f -- $REFERENCE_CATALOG_FILE)

    docker run \
        -t \
        -e API_CLIENT -e API_SECRET -e API_URL -e API_USER -e API_PASSWORD \
        -v "$ABSOLUTE_CATALOG_FILE:/app/akeneo-data-generator/app/catalog/product_api_catalog.yml" \
        akeneo/data-generator:3.0 akeneo:api:generate-catalog --with-products --check-minimal-install product_api_catalog.yml
}

setup_blackfire()
{
    echo "Install blackfire"

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
}

launch_bench()
{
    echo "Start benchmarks"

    API_RESPONSE=$(docker-compose exec -T fpm curl -s -X POST $API_URL/api/oauth/v1/token -H "authorization: Basic ${API_AUTH}" -H 'content-type: application/json' -d '{ "grant_type": "password", "username": "admin", "password": "admin" }')

    echo 'response'
    echo $API_RESPONSE

    API_TOKEN=$(echo $API_RESPONSE | jq -r '.access_token')

    echo $API_TOKEN

    docker-compose exec -T fpm curl "${API_URL}/api/rest/v1/products?limit=100" -H "authorization: Bearer ${API_TOKEN}" -H 'content-type: application/json'

    docker-compose exec -T fpm blackfire --samples 2 curl -X GET "${API_URL}/api/rest/v1/products?limit=100" -H "authorization: Bearer ${API_TOKEN}" -H 'content-type: application/json'
}

generate_reference_catalog
setup_blackfire
launch_bench

