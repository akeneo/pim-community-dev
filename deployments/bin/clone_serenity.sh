#!/bin/bash
set -eo pipefail
set -x

if [[ "${SOURCE_PFID}" == "" ]]; then
      echo "ERR : You must choose a prod source instance"
      exit 9
fi
if [ "${SOURCE_PED_TAG}" != "" ]; then
      echo "DEPRECATED : prod source version is retrieve by mysql disk pim_version label"
fi
if [[ "${INSTANCE_NAME}" == "" ]]; then
      echo "ERR : You must choose an instance name for the duplicate instance"
      exit 9
fi

BINDIR=$(dirname $(readlink -f $0))
PED_DIR="${BINDIR}/../../"

#
PFID="srnt-"${INSTANCE_NAME}
SOURCE_INSTANCE_NAME=$(echo ${SOURCE_PFID}| cut -c 6-)
DESTINATION_PATH=${DESTINATION_PATH:-${PED_DIR}/deployments/instances/${PFID}}
TARGET_PAPO_PROJECT_CODE=${TARGET_PAPO_PROJECT_CODE:-"NOT_ON_PAPO_${PFID}"}
DESTINATION_GOOGLE_CLUSTER_ZONE=${DESTINATION_GOOGLE_CLUSTER_ZONE:-"europe-west3-a"}
SOURCE_GOOGLE_PROJECT_ID=${SOURCE_GOOGLE_PROJECT_ID:-"akecld-saas-prod"}
DESTINATION_GOOGLE_PROJECT_ID="akecld-saas-dev"
TARGET_DNS_FQDN="${INSTANCE_NAME}.dev.cloud.akeneo.com."
#

echo "- Get mysql disk informations about prod source instance"
SELFLINKMYSQL=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-mysql-server-0" --limit=1 --sort-by="~creationTimestamp" --uri)
MYSQL_SIZE=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots describe $SELFLINKMYSQL --format=json | jq -r '.diskSizeGb')
SOURCE_PED_TAG=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots describe $SELFLINKMYSQL --format=json | jq -r '.labels.pim_version')

echo "- Upgrade config files"
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.installPim.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.addAdmin.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.upgradeES.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.resetPassword true
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.userPassword test
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.rootPassword test
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.email "adminakeneo"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.login "adminakeneo"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.password "adminakeneo"
yq w -i ${DESTINATION_PATH}/values.yaml mysql.common.persistentDisks[0] "${PFID}-mysql"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.papo_project_code' "${TARGET_PAPO_PROJECT_CODE}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.dns_external' "${TARGET_DNS_FQDN}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim-monitoring.source' "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform/monitoring?ref=${SOURCE_PED_TAG}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.source' "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${SOURCE_PED_TAG}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.pim_version' "${SOURCE_PED_TAG}"
# remove the old mysql_disk & mysql_source_snapshot if exit
yq d -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_name'
yq d -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_source_snapshot'
yq d -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_size'
# Add mysql_source_snapshot on the main.tf.json
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_source_snapshot' "${SELFLINKMYSQL}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_size' "${MYSQL_SIZE}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_name' "${PFID}-mysql"
cat ${DESTINATION_PATH}/main.tf.json

if [[ $CI != "true" ]]; then
      echo "Check your configurations files if needed : ${DESTINATION_PATH}"
      echo "Enter to continue, ^C to exit"
      read
fi

echo "- Run terraform & helm."
cd ${PED_DIR}; PIM_CONTEXT=deployment TF_INPUT_FALSE="-input=false" TF_AUTO_APPROVE="-auto-approve" INSTANCE_NAME=${INSTANCE_NAME}  IMAGE_TAG=${SOURCE_PED_TAG} INSTANCE_NAME_PREFIX=pimci-duplic make deploy

#echo "- Duplicate prod bucket... take long time..."
#cd ${PED_DIR}; gsutil -m rsync -r gs://${SOURCE_PFID} gs://${PFID}

echo "- Populate ES"
PODDAEMON=$(kubectl get pods --namespace=${PFID}|grep pim-daemon-default|head -n 1|awk '{print $1}')
kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console akeneo:elasticsearch:reset-indexes --env=prod --quiet && bin/console pim:product:index --all --env=prod && bin/console pim:product-model:index --all --env=prod'
kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console akeneo:asset-manager:index-assets --all'

echo "- Anonymize"
PODSQL=$(kubectl get pods --namespace=${PFID}|grep mysql|head -n 1|awk '{print $1}')
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE akeneo_connectivity_connection SET webhook_url = NULL, webhook_enabled = 0;"'
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE oro_user SET email = LOWER(CONCAT(SUBSTRING(CONCAT(\"support+clone_\", REPLACE(username,\"@\",\"_\")), 1, 64), \"@akeneo.com\"));"'
kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console pim:user:create adminakeneo adminakeneo product-team@akeneo.com admin1 admin2 en_US --admin -n || echo "WARN: User adminakeneo exists"'

echo "- Ensure that DQI evaluations will start"
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE akeneo_batch_job_execution SET exit_code=\"COMPLETED\" WHERE exit_code=\"UNKNOWN\" AND job_instance_id=(select id from akeneo_batch_job_instance WHERE code=\"data_quality_insights_evaluations\");"'

echo "- Upgrade config files"
yq d -i ${DESTINATION_PATH}/values.yaml pim.hook
