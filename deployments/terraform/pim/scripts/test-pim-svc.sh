#!/bin/sh
set -ex

CURL="curl --connect-timeout 10 --retry 5 --retry-delay 5 --retry-connrefused"

echo "Testing ${TARGET}"
timeout 500 curl --retry 300 --retry-delay 30 -k ${TARGET} > /dev/null

${CURL} --output - --header "${MONITORING_AUTHENTICATION_TOKEN}" ${TARGET}

exit 0
