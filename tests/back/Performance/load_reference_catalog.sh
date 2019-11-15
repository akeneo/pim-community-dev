#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$(dirname $0)

PIM_HTTPD_CONTAINER=$(docker-compose images | grep httpd | cut -d " " -f 1)
REFERENCE_CATALOG_FILE="$SCRIPT_DIR/reference_catalog.yml"
DOCKER_COMPOSE_RUN='docker-compose run -T -u www-data --rm'

echo "Reset the database with the minimal catalog"

$DOCKER_COMPOSE_RUN php bin/console pim:installer:db

echo "Generates an API user for the benchmarks in test environment"

$DOCKER_COMPOSE_RUN php bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

CREDENTIALS=$($DOCKER_COMPOSE_RUN php bin/console pim:oauth-server:create-client --no-ansi generator | tr -d '\r ')
export API_CLIENT=$(echo $CREDENTIALS | cut -d " " -f 2 | cut -d ":" -f 2)
export API_SECRET=$(echo $CREDENTIALS | cut -d " " -f 3 | cut -d ":" -f 2)
export API_URL="http://localhost"
export API_USER="admin"
export API_PASSWORD="admin"
export API_AUTH="$(echo -n $API_CLIENT:$API_SECRET | base64 -w 0 )"

echo "Generate the catalog"

docker pull akeneo/data-generator:2.0

ABSOLUTE_CATALOG_FILE=$(readlink -f -- $REFERENCE_CATALOG_FILE)

docker run \
  -t --network container:${PIM_HTTPD_CONTAINER} \
  -e API_CLIENT -e API_SECRET -e API_URL -e API_USER -e API_PASSWORD \
  -v "$ABSOLUTE_CATALOG_FILE:/app/akeneo-data-generator/app/catalog/product_api_catalog.yml" \
  akeneo/data-generator:2.0 akeneo:api:generate-catalog --with-products --check-minimal-install product_api_catalog.yml
