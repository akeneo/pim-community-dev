#!/bin/bash

set -eo pipefail
set -x

# How to:
#  cd /Terraform/dir/path ;TYPE=$(TYPE) INSTANCE_NAME=$(INSTANCE_NAME) bash $(PWD)/deployments/bin/delete_clone_flexibility.sh

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

TF_INPUT_FALSE="-input=false"
TF_AUTO_APPROVE="-auto-approve"

echo "1 - initializing terraform in $(pwd)"
terraform init

find ${NAMESPACE_PATH}/../../  -name "*.tf" -type f | xargs sed -i "s/prevent_destroy = true/prevent_destroy = false/g"
yq w -j -P -i ${PWD}/main.tf.json module.pim.force_destroy_storage true
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=module.pim.local_file.kubeconfig
terraform apply ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE} -target=module.pim.google_storage_bucket.srnt_bucket

echo "2 - removing deployment and terraform resources"
export KUBECONFIG=.kubeconfig
(helm list "${PFID}" | grep "${PFID}") && helm delete --purge ${PFID} || true
(kubectl get ns ${PFID} | grep "$PFID") && kubectl delete ns ${PFID} || true
terraform destroy ${TF_INPUT_FALSE} ${TF_AUTO_APPROVE}

echo "3 - Removing shared state files"
if [[ $GOOGLE_PROJECT_ID == "akecld-saas-dev" || $GOOGLE_PROJECT_ID == "akecld-onboarder-dev" ]]; then
        TF_BUCKET="-dev"
fi
echo gsutil -m rm -r gs://akecld-terraform${TF_BUCKET}/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${PFID}


echo "5 - Delete disks"
PV_NAME=$(kubectl get -n ${PFID} pvc -l role=mysql-server -o jsonpath='{.items[*].spec.volumeName}')
if [ -n "${PV_NAME}" ]; then
        PD_NAME=$(kubectl get pv "${PV_NAME}" -o jsonpath='{..spec.gcePersistentDisk.pdName}')
        echo "PV/PD ${PV_NAME} / ${PD_NAME} will be deleted"
fi
kubectl delete all,pvc --all -n ${PFID} --force --grace-period=0 && echo "kubectl delete all,pvc forced OK" || echo "WARNING: FAILED kubectl delete all,pvc --all -n ${PFID} --force --grace-period=0"
if [ -n "${PV_NAME}" ]; then kubectl delete pv ${PV_NAME}  && echo "SUCCEED to delete pv ${PV_NAME}" || echo "FAILED to delete pv ${PV_NAME}"; fi
if [ -n "${PD_NAME}" ]; then
	for i in {1..6}; do
		gcloud --quiet compute disks delete ${PD_NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE} && break || sleep 10
	done
fi

echo "- Delete disk ES disk (not managed by terraform)"
ES_DISK_NAME=${PFID}-es

if [[ $( gcloud compute disks describe ${ES_DISK_NAME} --zone=${GOOGLE_CLUSTER_ZONE} --project=${GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
      gcloud compute disks delete ${ES_DISK_NAME} --zone=${GOOGLE_CLUSTER_ZONE} --project=${GOOGLE_PROJECT_ID} --quiet
fi

MIGRATION_NAME=${PFID}-migration

if [[ $( gcloud compute disks describe ${MIGRATION_NAME} --zone=${GOOGLE_CLUSTER_ZONE} --project=${GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
      gcloud compute disks delete ${MIGRATION_NAME} --zone=${GOOGLE_CLUSTER_ZONE} --project=${GOOGLE_PROJECT_ID} --quiet
fi
