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
echo "  ----------------------- "


## Print original main.tf.json
echo "--- Original main.tf.json ---"
cat main.tf.json.ori
echo "-----------------------------"

## Add use_edition_flag=true dans main.tf.json
echo "--- Adding use_edition_flag in main.tf.json ---"
jq -r '.module.pim.use_edition_flag |= true ' main.tf.json > main.tf.json.temp && mv main.tf.json.temp main.tf.json
# TODO ->  Warning : pim-monitoring is depreacated -> Test before action
jq -r '.module."pim-monitoring".use_edition_flag |= "${module.pim.use_edition_flag}"' main.tf.json  > main.tf.json.temp && mv main.tf.json.temp main.tf.json

## Remove disk and snapshot from main.tf
echo "--- Delete mysql disk informations from main.tf.json ---"
jq -r '.module.pim.mysql_disk_name |= "" ' main.tf.json > main.tf.json.temp && mv main.tf.json.temp main.tf.json
jq -r '.module.pim.mysql_disk_description |= "" ' main.tf.json > main.tf.json.temp && mv main.tf.json.temp main.tf.json
jq -r '.module.pim.mysql_source_snapshot |= "" ' main.tf.json > main.tf.json.temp && mv main.tf.json.temp main.tf.json

## Print modified main.tf.json
echo "--- New main.tf.json -------"
cat main.tf.json
echo "-----------------------------"
## Print diff between Original and New  main.tf.json
echo "--- Difference between original and modified  main.tf.json -------"
diff main.tf.json.ori main.tf.json || true
echo "-----------------------------"

echo "--- Set minimal catalog and enable catalog installation helm hook ---"
yq w -i values.yaml pim.defaultCatalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal
yq w -i values.yaml pim.hook.installPim.enabled true

echo "--- values.yaml ---"
cat values.yaml

## Terraform init + Terraform Apply
echo "INFO: Receating Instance with Edition Flags ---"
terraform init -reconfigure -upgrade  "${TF_INPUT_FALSE}"
terraform plan '-out=upgrades.tfplan' "${TF_INPUT_FALSE}" -compact-warnings
terraform show -json upgrades.tfplan > upgrades.tfplan.json
HELM_DEBUG=true terraform apply "${TF_INPUT_FALSE}" "${TF_AUTO_APPROVE}" upgrades.tfplan

## TODO - Do we need to remove "pim.hook.installPim.enabled true" after deployments ?

echo "DEBUG: Show files in $PWD  ---"
ls -al

echo "INFO: Now files must be commited on cloud-customers"
