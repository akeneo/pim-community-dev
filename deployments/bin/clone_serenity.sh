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

PFID="srnt-"${INSTANCE_NAME}
SOURCE_INSTANCE_NAME=$(echo ${SOURCE_PFID}| cut -c 6-)
DESTINATION_PATH=${DESTINATION_PATH:-${PED_DIR}/deployments/instances/${PFID}}
TARGET_PAPO_PROJECT_CODE=${TARGET_PAPO_PROJECT_CODE:-"NOT_ON_PAPO_${PFID}"}
DESTINATION_GOOGLE_CLUSTER_ZONE=${DESTINATION_GOOGLE_CLUSTER_ZONE:-"europe-west3-a"}
SOURCE_GOOGLE_PROJECT_ID=${SOURCE_GOOGLE_PROJECT_ID:-"akecld-saas-prod"}
DESTINATION_GOOGLE_PROJECT_ID="akecld-saas-dev"
TARGET_DNS_FQDN="${INSTANCE_NAME}.dev.cloud.akeneo.com."

echo "- Get mysql and ES disk informations about prod source instance"
SELF_LINK_MYSQL=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-mysql-server-0" --limit=1 --sort-by="~creationTimestamp" --uri)
MYSQL_SIZE=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots describe ${SELF_LINK_MYSQL} --format=json | jq -r '.diskSizeGb')
SOURCE_PED_TAG=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots describe $SELF_LINK_MYSQL --format=json | jq -r '.labels.pim_version')
SELF_LINK_ES_MASTER_0=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-elasticsearch-master-0" --limit=1 --sort-by="~creationTimestamp" --uri)
SELF_LINK_ES_MASTER_1=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-elasticsearch-master-1" --limit=1 --sort-by="~creationTimestamp" --uri)
ES_MASTER_SIZE=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots describe ${SELF_LINK_ES_MASTER_0} --format=json | jq -r '.diskSizeGb')
if (($ES_MASTER_SIZE < 10)); then
    ES_MASTER_SIZE=10
fi
SELF_LINK_ES_DATA_0=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-elasticsearch-data-0" --limit=1 --sort-by="~creationTimestamp" --uri)
SELF_LINK_ES_DATA_1=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID} AND labels.pvc_name=data-elasticsearch-data-1" --limit=1 --sort-by="~creationTimestamp" --uri)
ES_DATA_SIZE=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots describe ${SELF_LINK_ES_DATA_0} --format=json | jq -r '.diskSizeGb')
if (($ES_DATA_SIZE < 10)); then
    ES_DATA_SIZE=10
fi


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
if [[ ${ACTIVATE_MONITORING} != "false" ]]; then
  yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim-monitoring.source' "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform/monitoring?ref=${SOURCE_PED_TAG}"
fi
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.source' "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${SOURCE_PED_TAG}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.pim_version' "${SOURCE_PED_TAG}"
# remove the old mysql_disk & mysql_source_snapshot if exit
yq d -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_name'
yq d -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_source_snapshot'
yq d -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_size'
# Add mysql_source_snapshot on the main.tf.json
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_source_snapshot' "${SELF_LINK_MYSQL}"
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

echo "- Create disk ES disk (delete before if existing)"
ES_PVC_MASTER_0=data-elasticsearch-master-0
ES_PVC_MASTER_1=data-elasticsearch-master-1
ES_PD_NAME_MASTER_0=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_MASTER_0 "$ES_PVC_MASTER_0" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_MASTER_0) | .spec.gcePersistentDisk.pdName] | .[]')
ES_PD_NAME_MASTER_1=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_MASTER_1 "$ES_PVC_MASTER_1" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_MASTER_1) | .spec.gcePersistentDisk.pdName] | .[]')
ES_PV_NAME_MASTER_0=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_MASTER_0 "$ES_PVC_MASTER_0" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_MASTER_0) | .metadata.name] | .[]')
ES_PV_NAME_MASTER_1=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_MASTER_1 "$ES_PVC_MASTER_1" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_MASTER_1) | .metadata.name] | .[]')
ES_PVC_DATA_0=data-elasticsearch-data-0
ES_PVC_DATA_1=data-elasticsearch-data-1
ES_PD_NAME_DATA_0=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_DATA_0 "$ES_PVC_DATA_0" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_DATA_0) | .spec.gcePersistentDisk.pdName] | .[]')
ES_PD_NAME_DATA_1=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_DATA_1 "$ES_PVC_DATA_1" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_DATA_1) | .spec.gcePersistentDisk.pdName] | .[]')
ES_PV_NAME_DATA_0=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_DATA_0 "$ES_PVC_DATA_0" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_DATA_0) | .metadata.name] | .[]')
ES_PV_NAME_DATA_1=$(kubectl get pv -o json | jq -r --arg PFID "$PFID" --arg ES_PVC_DATA_1 "$ES_PVC_DATA_1" ' [.items[] | select(.spec.claimRef.namespace == $PFID) | select(.spec.claimRef.name == $ES_PVC_DATA_1) | .metadata.name] | .[]')

create_disk_from_backup () {
      DISK_NAME=$1
      SELF_LINK_ES=$2
      DISK_SIZE=$3

      DISK_STATUS=$(gcloud compute disks create ${DISK_NAME} --size=${DISK_SIZE} --source-snapshot=${SELF_LINK_ES} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
      if [[ ${DISK_STATUS} == "" ]]; then
            echo "Duplicate disk does not exist, exiting"
            return 1
      fi
      echo "Duplicate disk has been created : ${DISK_STATUS}"
}

echo "- Scale down ES pods."
kubectl scale -n ${PFID} deploy/elasticsearch-client --replicas=0 --timeout=0s || true
kubectl scale -n ${PFID} statefulsets elasticsearch-data --replicas=0 --timeout=0s || true
kubectl scale -n ${PFID} statefulsets elasticsearch-master --replicas=0 --timeout=0s || true

echo "- Remove ES Persistent Disks master and data"
if [[ -n "${ES_PD_NAME_MASTER_0}" ]]; then
	for i in {1..6}; do
		gcloud --quiet compute disks delete ${ES_PD_NAME_MASTER_0} --project=${DESTINATION_GOOGLE_PROJECT_ID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} && break || sleep 10
	done
fi
if [[ -n "${ES_PD_NAME_MASTER_1}" ]]; then
	for i in {1..6}; do
		gcloud --quiet compute disks delete ${ES_PD_NAME_MASTER_1} --project=${DESTINATION_GOOGLE_PROJECT_ID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} && break || sleep 10
	done
fi
if [[ -n "${ES_PD_NAME_DATA_0}" ]]; then
	for i in {1..6}; do
		gcloud --quiet compute disks delete ${ES_PD_NAME_DATA_0} --project=${DESTINATION_GOOGLE_PROJECT_ID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} && break || sleep 10
	done
fi
if [[ -n "${ES_PD_NAME_DATA_1}" ]]; then
	for i in {1..6}; do
		gcloud --quiet compute disks delete ${ES_PD_NAME_DATA_1} --project=${DESTINATION_GOOGLE_PROJECT_ID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} && break || sleep 10
	done
fi

echo "- Create ES disk master and data"
create_disk_from_backup ${ES_PD_NAME_MASTER_0} ${SELF_LINK_ES_MASTER_0} ${ES_MASTER_SIZE}
create_disk_from_backup ${ES_PD_NAME_MASTER_1} ${SELF_LINK_ES_MASTER_1} ${ES_MASTER_SIZE}
create_disk_from_backup ${ES_PD_NAME_DATA_0} ${SELF_LINK_ES_DATA_0} ${ES_DATA_SIZE}
create_disk_from_backup ${ES_PD_NAME_DATA_1} ${SELF_LINK_ES_DATA_1} ${ES_DATA_SIZE}

echo "- Scale up ES pods"
kubectl scale -n ${PFID} deploy/elasticsearch-client --replicas=1 --timeout=0s || true
kubectl scale -n ${PFID} statefulsets elasticsearch-data --replicas=2 --timeout=0s || true
kubectl scale -n ${PFID} statefulsets elasticsearch-master --replicas=2 --timeout=0s || true

echo "- Anonymize"
PODSQL=$(kubectl get pods --namespace=${PFID} -l component=mysql | awk '/mysql/ {print $1}')
PODPIMWEB=$(kubectl get pods --no-headers --namespace=${PFID} -l component=pim-web | awk 'NR==1{print $1}')
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE akeneo_connectivity_connection SET webhook_url = NULL, webhook_enabled = 0;"'
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE oro_user SET email = LOWER(CONCAT(SUBSTRING(CONCAT(\"support+clone_\", REPLACE(username,\"@\",\"_\")), 1, 64), \"@akeneo.com\"));"'
kubectl exec -it -n ${PFID} ${PODPIMWEB} -- /bin/bash -c 'bin/console pim:user:create adminakeneo adminakeneo product-team@akeneo.com admin1 admin2 en_US --admin -n || echo "WARN: User adminakeneo exists"'

echo "- Check ES indexation"
(kubectl exec -it -n ${PFID} ${PODPIMWEB} -- /bin/bash -c 'bin/es_sync_checker --only-count') || true

# Workarround to be sure to have a admin user
SQLCOMMAND=$(cat ${BINDIR}/add_user_to_all_groups.sql)
# _ "${SQLCOMMAND}" -> allow to pass parameter
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "$@"' _ "${SQLCOMMAND}"


echo "- Ensure that DQI evaluations will start"
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE akeneo_batch_job_execution SET exit_code=\"COMPLETED\" WHERE exit_code=\"UNKNOWN\" AND job_instance_id=(select id from akeneo_batch_job_instance WHERE code=\"data_quality_insights_evaluations\");"'

echo "- Upgrade config files"
yq d -i ${DESTINATION_PATH}/values.yaml pim.hook
