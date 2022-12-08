#!/bin/bash

ENV_NAME_SHORTED="demo"
GOOGLE_CLOUD_PROJECT="akecld-prd-pim-saas-demo"
GOOGLE_CLOUD_FIRESTORE_PROJECT="akecld-prd-pim-fire-eur-demo"
GOOGLE_DOMAIN="demo.pim.akeneo.cloud"
GOOGLE_CLUSTER_NAME="akecld-prd-pim-saas-demo-europe-west3"
GOOGLE_CLUSTER_REGION="europe-west3"
GOOGLE_REGION_SHORTED="euw3"
GOOGLE_ZONE="europe-west3-b"
LOCATION="EU"
PREFIX_CLUSTER="demo-eur-w-3"

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
