#!/usr/bin/bash
set -x
set -e

apt-get -qq update
apt-get -qq --no-install-recommends --no-install-suggests --yes --quiet install curl ca-certificates

CURL="curl --connect-timeout 10 --retry 5 --retry-delay 5 --retry-connrefused"

echo "Testing ${TARGET}"
timeout 500 curl --retry 300 --retry-delay 30 -k ${TARGET} > /dev/null

${CURL} -L -m 5 -w "%{http_code}" ${TARGET}/api/rest/v1/products | grep 401

exit 0
