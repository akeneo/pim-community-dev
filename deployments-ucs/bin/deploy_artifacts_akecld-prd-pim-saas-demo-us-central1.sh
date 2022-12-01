#!/bin/bash

GOOGLE_CLOUD_PROJECT="akecld-prd-pim-saas-demo"
GOOGLE_CLOUD_FIRESTORE_PROJECT="akecld-prd-pim-fire-us-demo"
GOOGLE_DOMAIN="demo.pim.akeneo.cloud"
GOOGLE_CLUSTER_NAME="akecld-prd-pim-saas-demo-us-central1"
GOOGLE_CLUSTER_REGION="us-central1"
GOOGLE_ZONE="us-central1-b"
LOCATION="US"
PREFIX_CLUSTER="demo-us-c-1"

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
$SCRIPT_DIR/generate_values_file.sh \
    --project "${GOOGLE_CLOUD_PROJECT}" \
    --firestore-project "${GOOGLE_CLOUD_FIRESTORE_PROJECT}" \
    --domain "${GOOGLE_DOMAIN}" \
    --cluster "${GOOGLE_CLUSTER_NAME}" \
    --region "${GOOGLE_CLUSTER_REGION}" \
    --zone "${GOOGLE_ZONE}" \
    --location "${LOCATION}" \
    --cluster-prefix "${PREFIX_CLUSTER}"
