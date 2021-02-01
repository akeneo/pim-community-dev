#!/bin/bash

set -eo pipefail
set +x

# How to:
#  cd /Terraform/dir/path ; TYPE=$(TYPE) INSTANCE_NAME=$(INSTANCE_NAME) bash $(PWD)/deployments/bin/delete_instance.sh

if [[ ${INSTANCE_NAME} == "" ]]; then
        echo "ERR : You must choose an instance name for the instance to delete"
        exit 9
fi
if [[ ${TYPE} == "" ]]; then
        echo "WARN : set default value srnt for instance type to delete"
        TYPE="srnt"
fi

#
PFID="${TYPE}-${INSTANCE_NAME}"
GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
NAMESPACE_PATH=$(pwd)
#

echo "1 - initializing terraform in $(pwd)"
cat ${PWD}/main.tf.json
terraform init
# for mysql disk deletion, we must desactivate prevent_destroy in tf file
find ${NAMESPACE_PATH}/../../  -name "*.tf" -type f | xargs sed -i "s/prevent_destroy = true/prevent_destroy = false/g"
yq w -j -P -i ${PWD}/main.tf.json module.pim.force_destroy_storage true
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=module.pim.local_file.kubeconfig
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=module.pim.google_storage_bucket.srnt_bucket

echo "2 - removing deployment and terraform resources"
export KUBECONFIG=.kubeconfig
(helm list "${PFID}" | grep "${PFID}") && helm delete --purge ${PFID} || true
(kubectl get ns ${PFID} | grep "$PFID") && kubectl delete ns ${PFID} || true

LIST_PV_NAME=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" '[.items[] | select(.spec.claimRef.namespace == $PFID) | .metadata.name] | unique | .[]')

if [ -n "${PV_NAME}" ]; then
  RETRY=10
  while ((${RETRY}>0))
  do
    while read PV_NAME; do
      if [ -n "${PV_NAME}" ]; then
          PD_NAME=$(kubectl get pv "${PV_NAME}" -o jsonpath='{..spec.gcePersistentDisk.pdName}')
        if [ -n "${PD_NAME}" ]; then
          IS_DISK_IS_DETACHED=$(gcloud --project=${GOOGLE_PROJECT_ID}  compute disks list  --filter="(name=(${PD_NAME}) AND zone:europe-west3-a AND NOT users:*)" --format="value(name)" )
            if [ -z "$IS_DISK_IS_DETACHED" ]; then break; fi
        fi
      fi
    done <<< $LIST_PV_NAME
    if [ -n "$IS_DISK_DETACHED" ]; then break; fi
    ((RETRY--))
    sleep 5
  done
  if ((${RETRY}>0)); then echo "2.5 - All disk are detached"; else echo "2.5 - Some disks still attached, bye bye";exit 1; fi
fi

terraform destroy ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE}

echo "3 - Removing shared state files"
if [[ $GOOGLE_PROJECT_ID == "akecld-saas-dev" || $GOOGLE_PROJECT_ID == "akecld-onboarder-dev" ]]; then
        TF_BUCKET="-dev"
fi
# I'm sorry for that, but it's the max time communicate by google to apply consistent between list and delete operation on versionning bucket. See: https://cloud.google.com/storage/docs/object-versioning
sleep 30

gsutil rm -r gs://akecld-terraform${TF_BUCKET}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${PFID}

echo "5 - Delete disks"

while read PV_NAME; do
  if [ -n "${PV_NAME}" ]; then
      PD_NAME=$(kubectl get pv "${PV_NAME}" -o jsonpath='{..spec.gcePersistentDisk.pdName}')
      echo "PV/PD ${PV_NAME} / ${PD_NAME} will be deleted"
  fi

  if [ -n "${PV_NAME}" ]; then
    kubectl delete pv ${PV_NAME} --ignore-not-found=true && echo "SUCCEED to delete pv ${PV_NAME}" || echo "FAILED to delete pv ${PV_NAME}"
  fi
  if [ -n "${PD_NAME}" ]; then
    for i in {1..6}; do
      DISK_URI=$(gcloud compute disks list --filter="name=(${PD_NAME}) AND zone:(${GOOGLE_CLUSTER_ZONE})" --project ${GOOGLE_PROJECT_ID} --uri --quiet)
      if [ -z "$DISK_URI" ]; then break; fi
      gcloud --quiet compute disks delete ${DISK_URI} && break || sleep 30
    done
  fi
done <<< $LIST_PV_NAME
