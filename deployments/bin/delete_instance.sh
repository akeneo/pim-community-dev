#!/bin/bash

set -eo pipefail
set +x

# How to:
#  cd /Terraform/dir/path ; TYPE=$(TYPE) INSTANCE_NAME=$(INSTANCE_NAME) bash $(PWD)/deployments/bin/delete_instance.sh

if [[ ${INSTANCE_NAME} == "" ]]; then
        echo "ERR : You must choose an instance name for the instance to delete"
        exit 9
fi
if [[ ${IMAGE_TAG} == "" ]]; then
        echo "ERR : You must define your instance image tag"
        exit 10
fi
if [[ ${TYPE} == "" ]]; then
        echo "WARN : set default value srnt for instance type to delete"
        TYPE="srnt"
fi
if [[ $GOOGLE_PROJECT_ID == "akecld-saas-dev" || $GOOGLE_PROJECT_ID == "akecld-onboarder-dev" ]]; then
        TF_BUCKET="-dev"
fi

#
PFID="${TYPE}-${INSTANCE_NAME}"
GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
NAMESPACE_PATH=$(pwd)
#

echo "1 - initializing terraform in $(pwd)"
cat ${PWD}/main.tf.json

gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/${PFID}/default.tflock || true

if [[ ${PFID} =~ "grth" ]]; then
  SOURCE_PATH=$(cat ${PWD}/main.tf.json | jq -r '.module.pim.source')
  BUCKET_PATH=$(echo $SOURCE_PATH | sed 's/:https:\/\/www\.googleapis\.com\/storage\/v1/\//g' | sed 's/gcs/gs/g' | sed 's/\/\/deployments/\/deployments/g')

  # if //deployments doesn't exist we change it to /terraform/deployments
  echo "Source path : ${SOURCE_PATH}"
  echo "Check if bucket path \"${BUCKET_PATH}\" exists"
  BUCKET_EXIST=$(gsutil ls ${BUCKET_PATH})
  if [[ $? -ne 0 ]]; then
    echo "Bucket path \"${BUCKET_PATH}\" doesn't exist"
    echo "Change //deployments to /terraform/deployment"
    sed -i 's/\/deployments\/terraform/\/terraform\/deployments\/terraform/g' ${PWD}/main.tf.json
  fi
fi

if [[ ${PFID} =~ "tria" ]]; then
  echo "${PWD}/"
  gsutil cp gs://akecld-terraform${TF_BUCKET}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${PFID}/default.tfstate ${PWD}/
  TRIA_VAR=$(cat ${PWD}/default.tfstate | grep "akeneo_connect_saml_entity_id" || echo "")

  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_saml_entity_id
  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_saml_certificate
  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_saml_sp_client_id
  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_saml_sp_certificate_base64
  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_saml_sp_private_key_base64
  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_api_client_secret
  yq d -j -P -i ${PWD}/main.tf.json module.pim.akeneo_connect_api_client_password
  yq d -j -P -i ${PWD}/main.tf.json module.pim.ft_catalog_api_base_uri
  yq d -j -P -i ${PWD}/main.tf.json module.pim.ft_catalog_api_client_id
  yq d -j -P -i ${PWD}/main.tf.json module.pim.ft_catalog_api_password
  yq d -j -P -i ${PWD}/main.tf.json module.pim.ft_catalog_api_secret
  yq d -j -P -i ${PWD}/main.tf.json module.pim.ft_catalog_api_username
fi
terraform init
# for mysql disk deletion, we must desactivate prevent_destroy in tf file
find ${NAMESPACE_PATH}/../../  -name "*.tf" -type f | xargs sed -i "s/prevent_destroy = true/prevent_destroy = false/g"
yq w -j -P -i ${PWD}/main.tf.json module.pim.force_destroy_storage true
export TF_VAR_force_destroy_storage=true
terraform plan -target=module.pim.local_file.kubeconfig -target=module.pim.google_storage_bucket.srnt_bucket -target=module.pim.google_storage_bucket.srnt_es_bucket
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=module.pim.local_file.kubeconfig -target=module.pim.google_storage_bucket.srnt_bucket -target=module.pim.google_storage_bucket.srnt_es_bucket

echo "2 - removing deployment and terraform resources"
export KUBECONFIG=.kubeconfig

# WARNING ! DON'T DELETE release helm before get list of PD
# grep -v mysql because the mysql disk is manage by terraform process
LIST_PD_NAME=$((kubectl get pv -o json | jq -r --arg PFID "$PFID" '[.items[] | select(.spec.claimRef.namespace == $PFID) | .spec.gcePersistentDisk.pdName] | unique | .[]' | grep -v mysql) || echo "")

if helm3 list -n "${PFID}" | grep "${PFID}"; then
  helm3 uninstall ${PFID} -n ${PFID}
fi

echo "Remove Deployments"
# Quick fix and to remove after actual fix
LIST_DEPLOYMENTS=$(kubectl get deployment --no-headers --namespace=${PFID} | awk '{print $1}')
if [[ ! -z "${LIST_DEPLOYMENTS}" ]]; then
  kubectl delete deployment --grace-period=0 --namespace ${PFID} --ignore-not-found=true ${LIST_DEPLOYMENTS}
fi

echo "Remove Statefulset"
# Quick fix and to remove after actual fix
LIST_STATEFULSET=$(kubectl get statefulset --no-headers --namespace=${PFID} | awk '{print $1}')
if [[ ! -z "${LIST_STATEFULSET}" ]]; then
  kubectl delete statefulset --grace-period=0 --namespace ${PFID} --ignore-not-found=true ${LIST_STATEFULSET}
fi

echo "Remove PODS"
# Quick fix and to remove after actual fix
LIST_PODS=$(kubectl get pods --no-headers --namespace=${PFID} | awk '{print $1}')
if [[ ! -z "${LIST_PODS}" ]]; then
  kubectl delete pod --grace-period=0 --force --namespace ${PFID} --ignore-not-found=true ${LIST_PODS}
fi

echo "Wait MySQL deletion"
POD_MYSQL=$(kubectl get pods --no-headers --namespace=${PFID} -l component=mysql | awk '{print $1}')
if [[ ! -z "${POD_MYSQL}" ]]; then
  kubectl wait pod/${POD_MYSQL} --namespace=${PFID} --for=delete
fi

echo "Running terraform destroy"
terraform destroy ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE}

echo "3 - Removing shared state files"
# I'm sorry for that, but it's the max time communicate by google to apply consistent between list and delete operation on versionning bucket. See: https://cloud.google.com/storage/docs/object-versioning
sleep 30

gsutil rm -r gs://akecld-terraform${TF_BUCKET}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${PFID}

echo "4 - Delete PD, PVC and PV"
# Check disk still exist
if [[ -n "${LIST_PD_NAME}" ]]; then
  for PD_NAME in ${LIST_PD_NAME}; do
    IS_DISK_DETACHED=$(gcloud --project=${GOOGLE_PROJECT_ID} compute disks list --filter="(name=(${PD_NAME}) AND zone:${GOOGLE_CLUSTER_ZONE} AND NOT users:*)" --format="value(name)" )
    if [[ -z "$IS_DISK_DETACHED" ]]; then
      break;
    fi
    for i in {1..6}; do
  		gcloud --quiet compute disks delete ${PD_NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE} && break || sleep 10
  	done
  done
fi

# Remove PVC
# Empty list is not an error
LIST_PVC_NAME=$(kubectl get pvc -o json -n ${PFID} | jq -r '.items[].metadata.name' || echo "")
echo "PVC list : "
echo "${LIST_PVC_NAME}"
if [[ -n "${LIST_PVC_NAME}" ]]; then
  for PVC_NAME in ${LIST_PVC_NAME}; do
    echo "Delete pvc ${PVC_NAME}"
    kubectl delete pvc ${PVC_NAME} -n ${PFID}
  done
fi

# Remove PV
# Empty list is not an error
LIST_PV_NAME=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" '[.items[] | select(.spec.claimRef.namespace == $PFID) | .metadata.name] | unique | .[]' || echo "")
echo "PV list : "
echo "${LIST_PV_NAME}"
if [[ -n "${LIST_PV_NAME}" ]]; then
  for PV_NAME in ${LIST_PV_NAME}; do
    echo "Delete pv ${PV_NAME}"
    kubectl delete pv ${PV_NAME}
  done
fi

echo "5 - Delete policies and logging metrics"
LOGGING_METRIC=$(gcloud logging metrics list --project ${GOOGLE_PROJECT_ID} --filter="name ~ ${PFID}" --format="value(name)")
RELATED_ALERT=$(gcloud alpha monitoring policies list --project ${GOOGLE_PROJECT_ID} --filter="displayName ~ ${PFID}" --format="value(name)")
if [[ ${RELATED_ALERT} != "" ]]; then
    gcloud alpha monitoring policies delete ${RELATED_ALERT} --quiet --project ${GOOGLE_PROJECT_ID}
fi
if [[ ${LOGGING_METRIC} != "" ]]; then
    gcloud logging metrics delete ${LOGGING_METRIC} --quiet --project ${GOOGLE_PROJECT_ID}
fi

echo "6 - Delete namespace"
kubectl get ns ${PFID} && kubectl delete ns ${PFID}
