#!/usr/bin/bash
set -xe
apt-get -qq update
apt-get -qq --no-install-recommends --no-install-suggests --yes install wget ca-certificates
wget --tries 5 --retry-connrefused --retry-on-http-error=500 --retry-on-host-error --content-on-error --quiet --output-document - --header "${MONITORING_AUTHENTICATION_TOKEN}" ${TARGET}
