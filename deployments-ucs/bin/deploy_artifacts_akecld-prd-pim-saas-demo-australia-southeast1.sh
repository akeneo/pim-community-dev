#!/bin/bash

ENV_NAME_SHORTED="demo"
GOOGLE_CLOUD_PROJECT="akecld-prd-pim-saas-demo"
GOOGLE_CLOUD_FIRESTORE_PROJECT="akecld-prd-pim-fire-aus-demo"
GOOGLE_DOMAIN="demo.pim.akeneo.cloud"
GOOGLE_CLUSTER_NAME="akecld-prd-pim-saas-demo-australia-southeast1"
GOOGLE_CLUSTER_REGION="australia-southeast1"
GOOGLE_REGION_SHORTED="ause1"
GOOGLE_ZONE="australia-southeast1-b"
# No multi region exist for Australia
# https://cloud.google.com/storage/docs/locations#location-mr
LOCATION="AUSTRALIA-SOUTHEAST1"
PREFIX_CLUSTER="demo-aus-se-1"

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
$SCRIPT_DIR/generate_values_file.sh \
    --env "${ENV_NAME_SHORTED}" \
    --project "${GOOGLE_CLOUD_PROJECT}" \
    --firestore-project "${GOOGLE_CLOUD_FIRESTORE_PROJECT}" \
    --domain "${GOOGLE_DOMAIN}" \
    --cluster "${GOOGLE_CLUSTER_NAME}" \
    --region "${GOOGLE_CLUSTER_REGION}" \
    --region-shorted "${GOOGLE_REGION_SHORTED}" \
    --zone "${GOOGLE_ZONE}" \
    --location "${LOCATION}" \
    --cluster-prefix "${PREFIX_CLUSTER}"
