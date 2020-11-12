#!/bin/bash
set -eo pipefail
set -x

if [ "${SOURCE_PFID}" == "" ]; then
      echo "ERR : You must choose a prod source instance"
      exit 9
fi
if [ "${SOURCE_PED_TAG}" == "" ]; then
      echo "ERR : You must choose a prod source version for PED deployment"
      exit 9
fi
if [ "${INSTANCE_NAME}" == "" ]; then
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
DESTINATION_GOOGLE_CLUSTER_ZONE="europe-west3-a"
SOURCE_GOOGLE_PROJECT_ID="akecld-saas-prod"
DESTINATION_GOOGLE_PROJECT_ID="akecld-saas-dev"
TARGET_DNS_FQDN="${INSTANCE_NAME}.dev.cloud.akeneo.com."
#

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
yq w -i ${DESTINATION_PATH}/main.tf.json module.pim.papo_project_code "${TARGET_PAPO_PROJECT_CODE}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json module.pim.dns_external "${TARGET_DNS_FQDN}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json module.pim-monitoring.source "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform/monitoring?ref=${SOURCE_PED_TAG}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json module.pim.source "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${SOURCE_PED_TAG}"
cat ${DESTINATION_PATH}/main.tf.json

if [[ $CI != "true" ]]; then
      echo "Check your configurations files if needed : ${DESTINATION_PATH}"
      echo "Enter to continue, ^C to exit"
      read
fi

echo "- Get last snapshot selflink"
cd ${PED_DIR}
SELFLINKMYSQL=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-mysql-server-0" --limit=1 --sort-by="~creationTimestamp" --uri)
MYSQL_SIZE=$(gcloud compute snapshots describe $SELFLINKMYSQL --format=json | jq -r '.diskSizeGb')
echo "$SELFLINKMYSQL / $MYSQL_SIZE"

echo "- Create disk (delete before if existing)"
if [[ $( gcloud compute disks describe ${PFID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
      gcloud compute disks delete ${PFID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
      sleep 10
fi
DISKMYSQL=$(gcloud compute disks create ${PFID} --source-snapshot=${SELFLINKMYSQL} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
if [[ $DISKMYSQL == "" ]]; then
      echo "Mysql duplicate disk does not exist, exiting"
      exit 9
fi
echo "Mysql duplicate disk has been created : $DISKMYSQL"


echo "- Run terraform & helm."
cd ${PED_DIR}; TF_INPUT_FALSE="-input=false" TF_AUTO_APPROVE="-auto-approve" INSTANCE_NAME=${INSTANCE_NAME}  IMAGE_TAG=${SOURCE_PED_TAG} INSTANCE_NAME_PREFIX=pimci-duplic make deploy

#echo "- Duplicate prod bucket... take long time..."
#cd ${PED_DIR}; gsutil -m rsync -r gs://${SOURCE_PFID} gs://${PFID}

echo "- Populate ES"
PODDAEMON=$(kubectl get pods --namespace=${PFID}|grep pim-daemon-default|head -n 1|awk '{print $1}')
kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console akeneo:elasticsearch:reset-indexes --env=prod --quiet && bin/console pim:product:index --all --env=prod && bin/console pim:product-model:index --all --env=prod'

echo "- Anonymize"
PODSQL=$(kubectl get pods --namespace=${PFID}|grep mysql|head -n 1|awk '{print $1}')
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE oro_user SET email = LOWER(CONCAT(SUBSTRING(CONCAT(\"support+clone_\", username), 1, 64), \"@akeneo.com\"));"'
kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console pim:user:create adminakeneo adminakeneo product-team@akeneo.com admin1 admin2 en_US --admin -n'

echo "- Ensure that DQI evaluations will start"
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE akeneo_batch_job_execution SET exit_code=\"COMPLETED\" WHERE exit_code=\"UNKNOWN\" AND job_instance_id=(select id from akeneo_batch_job_instance WHERE code=\"data_quality_insights_evaluations\");"'

echo "- Upgrade config files"
yq d -i ${DESTINATION_PATH}/values.yaml pim.hook
