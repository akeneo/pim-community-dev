#!/bin/sh
set -ex

CURL="curl --connect-timeout 10 --retry 5 --retry-delay 5 --retry-connrefused"

echo "Testing bigcommerce connector for ${TARGET}"
timeout 500 curl --retry 300 --retry-delay 30 -k ${TARGET} > /dev/null

echo "Testing api for bigcommerce connector"
${CURL} -L -m 5 -w "%{http_code}" --user-agent "curl/akeneo-service-status" -k ${TARGET}/connectors/bigcommerce/api-web/service-status | grep 403

echo "Testing front for bigcommerce connector"
${CURL} -L -m 5 -w "%{http_code}" --user-agent "curl/akeneo-service-status" -k ${TARGET}/connectors/bigcommerce/configuration | grep 200

exit 0
