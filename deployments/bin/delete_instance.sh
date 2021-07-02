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
export TF_VAR_force_destroy_storage=true
terraform plan -target=module.pim.local_file.kubeconfig -target=module.pim.google_storage_bucket.srnt_bucket
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=module.pim.local_file.kubeconfig -target=module.pim.google_storage_bucket.srnt_bucket

echo "2 - removing deployment and terraform resources"
export KUBECONFIG=.kubeconfig

# WARNING ! DON'T DELETE release helm before get list of PD
# grep -v mysql because the mysql disk is manage by terraform process
LIST_PD_NAME=$((kubectl get pv -o json | jq -r --arg PFID "$PFID" '[.items[] | select(.spec.claimRef.namespace == $PFID) | .spec.gcePersistentDisk.pdName] | unique | .[]' | grep -v mysql) || echo "")

(helm3 list -n "${PFID}" && helm3 uninstall ${PFID} -n ${PFID}) || true
((kubectl get ns ${PFID} | grep "$PFID") && kubectl delete ns ${PFID}) || true

terraform destroy ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE}


echo "3 - Removing shared state files"
if [[ $GOOGLE_PROJECT_ID == "akecld-saas-dev" || $GOOGLE_PROJECT_ID == "akecld-onboarder-dev" ]]; then
        TF_BUCKET="-dev"
fi
# I'm sorry for that, but it's the max time communicate by google to apply consistent between list and delete operation on versionning bucket. See: https://cloud.google.com/storage/docs/object-versioning
sleep 30

gsutil rm -r gs://akecld-terraform${TF_BUCKET}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${PFID}

echo "4 - Delete PD and PV"
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

# Empty list is not an error
LIST_PV_NAME=$((kubectl get pv -o json | jq -r --arg PFID "$PFID" '[.items[] | select(.spec.claimRef.namespace == $PFID) | .metadata.name] | unique | .[]') || echo "")
if [[ -n "${LIST_PV_NAME}" ]]; then
  for PV_NAME in ${LIST_PV_NAME}; do
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
