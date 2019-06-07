#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$(dirname $0)
DOCKER_BRIDGE_IP=$(ip address show | grep "global docker" | cut -c10- | cut -d '/' -f1)
PUBLIC_PIM_HTTP_PORT=$(docker-compose port httpd-behat 80 | cut -d ':' -f 2)
REFERENCE_CATALOG_FILE="$SCRIPT_DIR/reference_catalog.yml"

echo "Reset the database with the minimal catalog"

docker-compose exec -T fpm bin/console pim:install:db -e behat

echo "Generates an API user for the benchmarks in test environment"

docker-compose exec -T fpm bin/console pim:user:create -e behat -- admin admin test@example.com John Doe en_US

CREDENTIALS=$(docker-compose exec -T fpm bin/console pim:oauth-server:create-client --no-ansi -e behat generator | tr -d '\r ')
export API_CLIENT=$(echo $CREDENTIALS | cut -d " " -f 2 | cut -d ":" -f 2)
export API_SECRET=$(echo $CREDENTIALS | cut -d " " -f 3 | cut -d ":" -f 2)
export API_URL="http://$DOCKER_BRIDGE_IP:${PUBLIC_PIM_HTTP_PORT}"
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
