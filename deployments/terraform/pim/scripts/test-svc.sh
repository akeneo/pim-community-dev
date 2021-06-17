#!/usr/bin/bash
set -x
set -e
apt-get -qq update
apt-get -qq --no-install-recommends --no-install-suggests --yes  install curl jq ca-certificates
curl -f -L --retry 5 --retry-delay 5 -m 10 -s -o /dev/null -H "${MONITORING_AUTHENTICATION_TOKEN}" ${TARGET} || exit 1 && exit 0
