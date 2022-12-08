#!/bin/bash

ENV_NAME_SHORTED="dev"
GOOGLE_CLOUD_PROJECT="akecld-prd-pim-saas-dev"
GOOGLE_CLOUD_FIRESTORE_PROJECT="akecld-prd-pim-fire-eur-dev"
GOOGLE_DOMAIN="ci.pim.akeneo.cloud"
GOOGLE_CLUSTER_NAME="akecld-prd-pim-saas-dev-europe-west1"
GOOGLE_CLUSTER_REGION="europe-west1"
GOOGLE_REGION_SHORTED="euw1"
GOOGLE_ZONE="europe-west1-b"
LOCATION="EU"
PREFIX_CLUSTER="dev-eur-w-1"

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
