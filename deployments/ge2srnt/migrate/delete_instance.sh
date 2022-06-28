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
PFID="${SOURCE_PFID}"
case ${ENV_NAME} in
    dev ) ENV_SUFFIX="-dev" ;;
    prod ) ENV_SUFFIX="" ;;
esac

echo " -- Display ENV_VARS : --"
echo "PFID=${PFID}"
echo "GOOGLE_PROJECT_ID=${GOOGLE_PROJECT_ID}"
echo "GOOGLE_CLUSTER_ZONE=${GOOGLE_CLUSTER_ZONE}"
echo "TF_INPUT_FALSE=${TF_INPUT_FALSE}"
echo "TF_AUTO_APPROVE=${TF_AUTO_APPROVE}"
echo "  -----------------------"


echo "1 - initializing terraform in $(pwd)"
cat "${PWD}"/main.tf.json

gsutil rm gs://akecld-terraform"${ENV_SUFFIX}"/saas/"${GOOGLE_PROJECT_ID}"/${GOOGLE_CLUSTER_ZONE}/"${PFID}"/default.tflock || true

if [[ ${PFID} =~ "grth" ]]; then
  SOURCE_PATH=$(cat "${PWD}"/main.tf.json | jq -r '.module.pim.source')
  BUCKET_PATH=$(echo "$SOURCE_PATH" | sed 's/:https:\/\/www\.googleapis\.com\/storage\/v1/\//g' | sed 's/gcs/gs/g' | sed 's/\/\/deployments/\/deployments/g')

  # if //deployments doesn't exist we change it to /terraform/deployments
  echo "Source path : ${SOURCE_PATH}"
  echo "Check if bucket path \"${BUCKET_PATH}\" exists"
  BUCKET_EXIST=$(gsutil ls "${BUCKET_PATH}")
  if [[ $? -ne 0 ]]; then
    echo "Bucket path \"${BUCKET_PATH}\" doesn't exist"
    echo "Change //deployments to /terraform/deployment"
    sed -i 's/\/deployments\/terraform/\/terraform\/deployments\/terraform/g' "${PWD}"/main.tf.json
  fi
fi

terraform init

TARGET=module.pim.local_file.kubeconfig
terraform apply "${TF_INPUT_FALSE}" "${TF_AUTO_APPROVE}" -compact-warnings -target=${TARGET}

echo "2 - removing deployment and terraform resources"
export KUBECONFIG=.kubeconfig

if helm3 list -n "${PFID}" | grep "${PFID}"; then
  helm3 uninstall "${PFID}" -n "${PFID}"
fi


# Force Delete if Helm Uninstall issues
echo "Remove Deployments"
LIST_DEPLOYMENTS=$(kubectl get deployment --no-headers --namespace="${PFID}" | awk '{print $1}')
if [[ -n "${LIST_DEPLOYMENTS}" ]]; then
  kubectl delete deployment --grace-period=0 --namespace "${PFID}" --ignore-not-found=true "${LIST_DEPLOYMENTS}"
fi

echo "Remove Statefulset"
LIST_STATEFULSET=$(kubectl get statefulset --no-headers --namespace="${PFID}" | awk '{print $1}')
if [[ -n "${LIST_STATEFULSET}" ]]; then
  kubectl delete statefulset --grace-period=0 --namespace "${PFID}" --ignore-not-found=true "${LIST_STATEFULSET}"
fi

echo "Remove PODS"
LIST_PODS=$(kubectl get pods --no-headers --namespace="${PFID}" -l 'app notin (mysql,elasticsearch)' | awk '{print $1}')
if [[ -n "${LIST_PODS}" ]]; then
  kubectl delete pod --grace-period=0 --force --namespace "${PFID}" --ignore-not-found=true "${LIST_PODS}"
fi

echo "Remove PODS with disks"
LIST_PODS=$(kubectl get pods --no-headers --namespace="${PFID}" -l 'app in (mysql,elasticsearch)' | awk '{print $1}')
if [[ -n "${LIST_PODS}" ]]; then
  kubectl delete pod --grace-period=0 --namespace "${PFID}" --ignore-not-found=true "${LIST_PODS}"
fi

echo "Wait MySQL deletion"
POD_MYSQL=$(kubectl get pods --no-headers --namespace="${PFID}" -l component=mysql | awk '{print $1}')
if [[ -n "${POD_MYSQL}" ]]; then
  kubectl wait pod/"${POD_MYSQL}" --namespace="${PFID}" --for=delete
fi

echo "3 - Delete PVC, PV and PD"
# Remove PVC
# Empty list is not an error
LIST_PVC_NAME=$(kubectl get pvc -o json -n "${PFID}" | jq -r '.items[].metadata.name' || echo "")
echo "PVC list : "
echo "${LIST_PVC_NAME}"
if [[ -n "${LIST_PVC_NAME}" ]]; then
  for PVC_NAME in ${LIST_PVC_NAME}; do
    echo "Delete pvc ${PVC_NAME}"
    kubectl delete pvc "${PVC_NAME}" -n "${PFID}"
  done
fi

# Remove PV
# Empty list is not an error
# Filter Mysql PV to remove flackyness
LIST_PV_NAME=$(kubectl get pv -o json -l app!=mysql | jq -r --arg PFID "$PFID" '[.items[] | select(.spec.claimRef.namespace == $PFID) | .metadata.name] | unique | .[]' || echo "")
echo "PV list : "
echo "${LIST_PV_NAME}"
if [[ -n "${LIST_PV_NAME}" ]]; then
  for PV_NAME in ${LIST_PV_NAME}; do
    echo "Delete pv ${PV_NAME}"
    kubectl delete pv "${PV_NAME}"
  done
fi

echo "5 - Delete policies and logging metrics"
LOGGING_METRIC=$(gcloud logging metrics list --project "${GOOGLE_PROJECT_ID}" --filter="name ~ ${PFID}" --format="value(name)")
RELATED_ALERT=$(gcloud alpha monitoring policies list --project "${GOOGLE_PROJECT_ID}" --filter="displayName ~ ${PFID}" --format="value(name)")
if [[ ${RELATED_ALERT} != "" ]]; then
    gcloud alpha monitoring policies delete "${RELATED_ALERT}" --quiet --project "${GOOGLE_PROJECT_ID}"
fi
if [[ ${LOGGING_METRIC} != "" ]]; then
    gcloud logging metrics delete "${LOGGING_METRIC}" --quiet --project "${GOOGLE_PROJECT_ID}"
fi

echo "6 - Delete namespace"
kubectl delete ns "${PFID}" --ignore-not-found=true || true

echo "7 - Running terraform destroy (except for bucket and mysql disk)"

TF_STATE_LIST_WITHOUT_BUCKET_AND_DISK=$(terraform state list | grep -v module.pim.google_compute_disk.mysql-disk | grep -v module.pim.google_storage_bucket )
for FILTERED_TARGET in $TF_STATE_LIST_WITHOUT_BUCKET_AND_DISK
  do
    terraform destroy "${TF_INPUT_FALSE}" "${TF_AUTO_APPROVE}" -compact-warnings -target="${FILTERED_TARGET}"
  done
