#!/usr/bin/env bash

set -eu
CURRENT_TIME=$(date +%s)
LAST_HOUR_TIME=$(( CURRENT_TIME - 60*60 ))
curl --location -s -g -H "Content-Type: application/json" -H "DD-API-KEY: ${DATADOG_API_KEY}" -H "DD-APPLICATION-KEY: ${DATADOG_APP_KEY}" --request GET "https://api.datadoghq.eu/api/v1/query?from=${LAST_HOUR_TIME}&to=${CURRENT_TIME}&query=top(sum:kubernetes.containers.running{project:akecld-saas-prod,*,*,short_image:pim-enterprise-dev,app:pim,component:pim-web,*,type:${TYPE}}%20by%20{image_tag},%201,%20%27max%27,%20%27desc%27)" | jq -r .series[0].tag_set[0] | cut -c11-
