#!/bin/sh
set -ex

CURL="curl --connect-timeout 10 --retry 5 --retry-delay 5 --retry-connrefused --http1.1"

echo "Testing ${TARGET}"
timeout 500 curl --retry 300 --retry-delay 30 -k ${TARGET} --http1.1 > /dev/null

${CURL} -L -m 5 -w "%{http_code}" ${TARGET}/api/rest/v1/products | grep 401

exit 0
