#!/bin/bash
set -euo pipefail

# Summary:
# Deleting all ressources from an instances except mysql disks and google storage bucket

# For JenkinsFile Runner mode
if [[ ${ENV_NAME:-} == "dev" ]]
then
    GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
    GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
    SOURCE_PFID=${PFID:-${TYPE}-${INSTANCE_NAME}}
fi

echo "--- Enter in Delete Instance Script ---"

if [[ -z  "${SOURCE_PFID:-}" ]]; then
    echo "ERR : You must choose an SOURCE_PFID  for the instance to delete."
    exit 9
fi
if [[ ! ${ENV_NAME:-} =~ ^(dev|prod)$ ]]; then
    echo "ERROR: environment variable ENV_NAME must be : dev or prod"
    exit 9
fi
if [[ -z  "${GOOGLE_PROJECT_ID:-}" ]]; then
    echo "WARN : GOOGLE_PROJECT_ID is  not set."
    exit 9
fi
if [[ -z "${GOOGLE_CLUSTER_ZONE=-}" ]]; then
    echo "WARN : GOOGLE_CLUSTER_ZONE is  not set."
    exit 9
fi

## Init Vars ( Default values )
TF_INPUT_FALSE=${TF_INPUT_FALSE:--input=false}
TF_AUTO_APPROVE=${TF_AUTO_APPROVE:--auto-approve}
# Calculated Vars
case ${ENV_NAME} in
    dev ) ENV_SUFFIX="-dev" ;;
    prod ) ENV_SUFFIX="" ;;
esac

echo " -- Display ENV_VARS : --"
echo "SOURCE_PFID=${SOURCE_PFID}"
echo "GOOGLE_PROJECT_ID=${GOOGLE_PROJECT_ID}"
echo "GOOGLE_CLUSTER_ZONE=${GOOGLE_CLUSTER_ZONE}"
echo "TF_INPUT_FALSE=${TF_INPUT_FALSE}"
echo "TF_AUTO_APPROVE=${TF_AUTO_APPROVE}"
echo "  -----------------------"

echo "1 - initializing terraform in $(pwd)"
cat "${PWD}"/main.tf.json

gsutil rm gs://akecld-terraform"${ENV_SUFFIX}"/saas/"${GOOGLE_PROJECT_ID}"/${GOOGLE_CLUSTER_ZONE}/"${SOURCE_PFID}"/default.tflock || true

terraform init

# for mysql disk deletion, we must desactivate prevent_destroy in tf file
find -L ${PWD} -name "*.tf" -type f | xargs sed -i "s/prevent_destroy = true/prevent_destroy = false/g"
yq w -j -P -i ${PWD}/main.tf.json module.pim.force_destroy_storage true

TF_STATE_LIST=$(terraform state list)

export TF_VAR_force_destroy_storage=true

echo "Check if srnt_bucket exists and need update to remove prevent destroy"
TARGET=module.pim.google_storage_bucket.srnt_bucket
if [[ -n $(echo ${TF_STATE_LIST} | grep "${TARGET}") ]]; then
  terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=${TARGET}
fi

echo "Check if srnt_es_bucket exists and need update to remove prevent destroy"
TARGET=module.pim.google_storage_bucket.srnt_es_bucket
if [[ -n $(echo ${TF_STATE_LIST} | grep "${TARGET}") ]]; then
  terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=${TARGET}
fi

echo "Running terraform destroy"
terraform destroy ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE}

echo "3 - Removing shared state files"
# I'm sorry for that, but it's the max time communicate by google to apply consistent between list and delete operation on versionning bucket. See: https://cloud.google.com/storage/docs/object-versioning
sleep 30

gsutil rm -r gs://akecld-terraform${ENV_SUFFIX}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${SOURCE_PFID} || echo "FAILED : gsutil rm -r gs://akecld-terraform${ENV_SUFFIX}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${SOURCE_PFID}"
