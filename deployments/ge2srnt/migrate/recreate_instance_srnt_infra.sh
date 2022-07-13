#!/bin/bash
set -euo pipefail

# Summary:
# 1 - Recrating instances with Cloud Customers folder prefixed by srnt-...
# grth-{$INSTANCE=NAME} become srnt-${INSTANCE-NAME}
# 2 - Add use_edition_flag=true in instance main.tf.json
# 3 - Replace version and edition in main.tf.json
# 4 - Change defaultCatalog and active installPim hook
# 5 - Redeploy instance with Terraform
# 6 - Commit new instances in folder prefix by srnt-... in cloud-customers

# For JenkinsFile Runner mode
if [[ ${ENV_NAME:-} == "dev" ]]
then
    GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
    GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
    SOURCE_PFID=${SOURCE_PFID:-${TYPE}-${INSTANCE_NAME}}
fi

echo "--- Enter in Recreate Instance Script ---"

# Input Needed for the script
if [[ -z  "${SOURCE_PFID:-}" ]]; then
    echo "ERR : You must choose an SOURCE_PFID for the instance to recreate."
    exit 9
fi
if [[ ! ${ENV_NAME:-} =~ ^(dev|prod)$ ]]; then
    echo "ERROR: environment variable ENV_NAME must be : dev or prod."
    exit 9
fi
if [[ -z  "${GOOGLE_PROJECT_ID:-}" ]]; then
    echo "ERROR: environment variable is  not set, it will use default value."
fi
if [[ -z "${GOOGLE_CLUSTER_ZONE=-}" ]]; then
    echo "ERROR: environment variable is  not set, it will use default value."
fi

## Init Vars ( Default values )
INSTANCE_NAME=${INSTANCE_NAME:-$(print "${SOURCE_PFID}" | cut -d "-" -f 2-)}
TARGET_PFID="srnt-${INSTANCE_NAME}"
TF_INPUT_FALSE=${TF_INPUT_FALSE:--input=false}
TF_AUTO_APPROVE=${TF_AUTO_APPROVE:--auto-approve}
# Calculated Vars
case ${ENV_NAME} in
    dev ) ENV_SUFFIX="-dev" ;;
    prod ) ENV_SUFFIX="" ;;
esac

echo " --- Display ENV_VARS : ---"
echo "SOURCE_PFID=${SOURCE_PFID}"
echo "TARGET_PFID=${TARGET_PFID}"
echo "GOOGLE_PROJECT_ID=${GOOGLE_PROJECT_ID}"
echo "GOOGLE_CLUSTER_ZONE=${GOOGLE_CLUSTER_ZONE}"
echo "ENV=${ENV_NAME}"
echo "TF_INPUT_FALSE=${TF_INPUT_FALSE}"
echo "TF_AUTO_APPROVE=${TF_AUTO_APPROVE}"
echo "---------------------------------------------------"

## Terraform Init / Plan / Apply
echo "INFO: Receating Instance with Edition Flags"
echo "--- terraform init step ---"
terraform init -reconfigure -upgrade  "${TF_INPUT_FALSE}"
echo "---------------------------------------------------"
echo "--- terraform plan step ---"
terraform plan '-out=upgrades.tfplan' "${TF_INPUT_FALSE}" -compact-warnings
echo "---------------------------------------------------"
echo "--- terraform show step ---"
terraform show -json upgrades.tfplan > upgrades.tfplan.json
echo "---------------------------------------------------"
echo "--- terraform apply step ---"
HELM_DEBUG=true terraform apply "${TF_INPUT_FALSE}" "${TF_AUTO_APPROVE}" upgrades.tfplan

echo "INFO: Remove minimal catalog after instance recreation"
yq d -i values.yaml pim.defaultCatalog
yq d -i values.yaml pim.hook.installPim

echo "DEBUG: Show files in $PWD  ---"
ls -al

echo "INFO: Now files must be commited on cloud-customers"
