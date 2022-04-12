#!/bin/bash
set -eo pipefail
set -x

if [[ "${SOURCE_PFID}" == "" ]]; then
      echo "ERR : You must choose a prod source instance"
      exit 9
fi
if [[ "${SOURCE_PROJECT_ID}" == "" ]]; then
      echo "ERR : You must choose source project for the instance"
      exit 9
fi
if [[ "${PED_TAG}" == "" ]]; then
      echo "ERR : You must choose a prod source version for PED deployment"
      exit 9
fi
if [[ "${INSTANCE_NAME}" == "" ]]; then
      echo "ERR : You must choose an instance name for the duplicate instance"
      exit 9
fi
if [[ "${BUCKET}" == "" ]]; then
      echo "ERR : You must specify the BUCKET directory"
      exit 9
fi

BINDIR=$(dirname $(readlink -f $0))
PED_DIR="${BINDIR}/../../"

PFID="srnt-"${INSTANCE_NAME}
SOURCE_INSTANCE_NAME=${SOURCE_PFID}
DESTINATION_PATH=${DESTINATION_PATH:-${PED_DIR}/deployments/instances/${PFID}}
TARGET_PAPO_PROJECT_CODE=${TARGET_PAPO_PROJECT_CODE:-"NOT_ON_PAPO_${PFID}"}
DESTINATION_GOOGLE_PROJECT_ID="akecld-saas-dev"
DESTINATION_GOOGLE_CLUSTER_ZONE=${DESTINATION_GOOGLE_CLUSTER_ZONE:-"europe-west3-a"}
SOURCE_GOOGLE_PROJECT_ID=${SOURCE_GOOGLE_PROJECT_ID:-"akecld-saas-prod"}
SOURCE_GOOGLE_CLUSTER_ZONE=${SOURCE_GOOGLE_CLUSTER_ZONE:-"europe-west2-b"}
TARGET_DNS_FQDN="${INSTANCE_NAME}.dev.cloud.akeneo.com."
PIM_USER="adminakeneo"
PIM_PASSWORD=$(pwgen -s 32 1)
MYSQL_ROOT_PASSWORD=$(pwgen -s 16 1)
MYSQL_USER_PASSWORD=$(pwgen -s 16 1)



echo "#########################################################################"
echo "- Get last snapshot selflink and create ES and MySQL disk"
SELFLINK_FLEX=$(gcloud --project="${SOURCE_PROJECT_ID}" compute snapshots list --filter="name=${SOURCE_PFID}-disk" --limit=1 --sort-by="~creationTimestamp" --uri)
FLEX_SIZE=$(gcloud compute snapshots describe ${SELFLINK_FLEX} --format=json | jq -r '.diskSizeGb')
echo "${SELFLINK_FLEX} / ${FLEX_SIZE}"

echo "- Create Flex disk (delete it before if existing)"
FLEX_DISK_NAME=${PFID}-flex
if [[ $( gcloud compute disks describe ${FLEX_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
      gcloud compute disks delete ${FLEX_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
      sleep 10
fi
FLEX_DISK=$(gcloud compute disks create ${FLEX_DISK_NAME} --source-snapshot=${SELFLINK_FLEX} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
if [[ ${FLEX_DISK} == "" ]]; then
      echo "Flex duplicate disk does not exist, exiting"
      return 1
fi
echo "Flex duplicate disk has been created : ${FLEX_DISK}"

echo "- Create ES disk (delete it before if existing)"
ES_DISK_NAME=${PFID}-es
ES_DISK_SIZE=${FLEX_SIZE}
if [[ $( gcloud compute disks describe ${ES_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
      gcloud compute disks delete ${ES_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
      sleep 10
fi
ES_DISK=$(gcloud compute disks create ${ES_DISK_NAME} --size=${ES_DISK_SIZE} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
if [[ ${ES_DISK} == "" ]]; then
      echo "ES duplicate disk does not exist, exiting"
      return 1
fi
echo "ES duplicate disk has been created : ${ES_DISK}"

echo "- Create MySQL disk (delete it before if existing)"
MYSQL_DISK_NAME=${PFID}-mysql
MYSQL_DISK_SIZE=${FLEX_SIZE}
if [[ $( gcloud compute disks describe ${MYSQL_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
      gcloud compute disks delete ${MYSQL_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
      sleep 10
fi
MYSQL_DISK=$(gcloud compute disks create ${MYSQL_DISK_NAME} --size=${MYSQL_DISK_SIZE} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
if [[ ${MYSQL_DISK} == "" ]]; then
      echo "Mysql duplicate disk does not exist, exiting"
      return 1
fi
echo "Mysql duplicate disk has been created : ${MYSQL_DISK}"


echo "#########################################################################"
echo "- Build a Flex into Serenity helm chart on the Fly!"
yq d -i ${PED_DIR}/deployments/terraform/pim/Chart.yaml "dependencies.(name==elasticsearch)"
yq w -i ${PED_DIR}/deployments/terraform/pim/Chart.yaml "dependencies[+].name" "flex-es"
yq w -i ${PED_DIR}/deployments/terraform/pim/Chart.yaml "dependencies.(name==flex-es).alias" "elasticsearch"
yq w -i ${PED_DIR}/deployments/terraform/pim/Chart.yaml "dependencies.(name==flex-es).repository" "file://${PED_DIR}/deployments/share/flex-es/"
yq w -i ${PED_DIR}/deployments/terraform/pim/Chart.yaml "dependencies.(name==flex-es).version" "0.0.1"

yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.addAdmin.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.installPim.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.upgradeES.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.resetPassword true
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.rootPassword "${MYSQL_ROOT_PASSWORD}"
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.userPassword "${MYSQL_USER_PASSWORD}"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.email "${PIM_USER}"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.login "${PIM_USER}"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.password "${PIM_PASSWORD}"
# Copy the values.yaml file to be use when applying SRNT chart
cp ${DESTINATION_PATH}/values.yaml /tmp/values.yaml
yq d -i ${DESTINATION_PATH}/values.yaml "elasticsearch"

yq d -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch"
# Not used, but to be compatible with helm using the Tshirt size
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.client.heapSize" "512m"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.client.resources.limits.memory" "1024Mi"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.client.resources.requests.cpu" "20m"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.client.resources.requests.memory" "1024Mi"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.master.heapSize" "384m"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.master.resources.limits.memory" "768Mi"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.master.resources.requests.cpu" "15m"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.master.resources.requests.memory" "768Mi"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.data.heapSize" "1024m"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.data.resources.limits.memory" "1740Mi"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.data.resources.requests.cpu" "40m"
yq w -i ${PED_DIR}/deployments/terraform/pim/values.yaml "elasticsearch.data.resources.requests.memory" "1536Mi"
# Not used, but to be compatible with helm using the Tshirt size

yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.appVersion" "7.14.1"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.common.service.name" "elasticsearch-client"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.cluster.plugins[+]" "repository-gcs"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.fullName" "elasticsearch"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.fullnameOverride" "elasticsearch"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.image.es.tag" "7.14.1"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.single.persistentDisk.name" "${ES_DISK_NAME}"
yq w -i ${DESTINATION_PATH}/values.yaml "elasticsearch.single.persistentDisk.size" "${FLEX_SIZE}"



yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_size' "${MYSQL_DISK_SIZE}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.mysql_disk_name' "${MYSQL_DISK_NAME}"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.source' "../../terraform"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim-monitoring.source' "../../terraform/monitoring"

# Copy the main.tf.json file to be use when applying monitoring module
cp ${DESTINATION_PATH}/main.tf.json /tmp/main.tf.json
# Delete the module.pim-monitoring for now to be able to import MySQL disk
yq d -P -j -i ${DESTINATION_PATH}/main.tf.json "module.pim-monitoring"


echo "#########################################################################"
echo "- Generate kubeconfig -"
cd ${DESTINATION_PATH}
terraform init
terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig


echo "#########################################################################"
echo "- Prepare Mysql and ES disk"
export KUBECONFIG=.kubeconfig

kubectl describe namespace ${PFID} || kubectl create namespace ${PFID}
FLEX_DISK_NAME=${FLEX_DISK_NAME} ES_DISK_NAME=${ES_DISK_NAME} SOURCE_PFID=${SOURCE_PFID}  NAMESPACE=${PFID} envsubst < ${BINDIR}/../share/es-data-move.yaml.tpl | kubectl -n ${PFID} apply -f -
kubectl -n ${PFID} wait --for=condition=complete --timeout=5m job/es-data-move
FLEX_DISK_NAME=${FLEX_DISK_NAME} ES_DISK_NAME=${ES_DISK_NAME} SOURCE_PFID=${SOURCE_PFID}  NAMESPACE=${PFID} envsubst < ${BINDIR}/../share/es-data-move.yaml.tpl | kubectl -n ${PFID} delete -f -

# Mysql modification to be able to use the SRNT MySQL chart
FLEX_DISK_NAME=${FLEX_DISK_NAME} MYSQL_DISK_NAME=${MYSQL_DISK_NAME} SOURCE_PFID=${SOURCE_PFID} NAMESPACE=${PFID}  envsubst < ${BINDIR}/../share/mysql-data-move.yaml.tpl | kubectl -n ${PFID} apply -f -
kubectl -n ${PFID} wait --for=condition=complete --timeout=5m job/mysql-data-move
FLEX_DISK_NAME=${FLEX_DISK_NAME} MYSQL_DISK_NAME=${MYSQL_DISK_NAME} SOURCE_PFID=${SOURCE_PFID} NAMESPACE=${PFID}  envsubst < ${BINDIR}/../share/mysql-data-move.yaml.tpl | kubectl -n ${PFID} delete -f -
MYSQL_DISK_NAME=${MYSQL_DISK_NAME} MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD} MYSQL_USER_PASSWORD=${MYSQL_USER_PASSWORD} SOURCE_PFID=${SOURCE_PFID} NAMESPACE=${PFID}  envsubst < ${BINDIR}/../share/mysql-fix-init.yaml.tpl | kubectl -n ${PFID} apply -f -
kubectl -n ${PFID} wait --for=condition=complete --timeout=2m job/mysql-fix-init
MYSQL_DISK_NAME=${MYSQL_DISK_NAME} MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD} MYSQL_USER_PASSWORD=${MYSQL_USER_PASSWORD} SOURCE_PFID=${SOURCE_PFID} NAMESPACE=${PFID}  envsubst < ${BINDIR}/../share/mysql-fix-init.yaml.tpl | kubectl -n ${PFID} delete -f -

# Import MySQL disk
terraform import module.pim.google_compute_disk.mysql-disk projects/${DESTINATION_GOOGLE_PROJECT_ID}/zones/${DESTINATION_GOOGLE_CLUSTER_ZONE}/disks/${MYSQL_DISK_NAME}


echo "#########################################################################"
echo "- Create Flex/Serenity instance"
# Revert the main.tf.json file for use monitoring
cp /tmp/main.tf.json ${DESTINATION_PATH}/main.tf.json
terraform init
terraform apply -input=false -auto-approve


echo "#########################################################################"
echo "- Copy the asset and catalog storages"
FLEX_DISK_NAME=${FLEX_DISK_NAME} PFID=${PFID} NAMESPACE=${PFID} envsubst < ${BINDIR}/../share/asset-move.yaml.tpl | kubectl -n ${PFID} apply -f -
kubectl -n ${PFID} wait --for=condition=complete --timeout=9m job/asset-move
FLEX_DISK_NAME=${FLEX_DISK_NAME} PFID=${PFID} NAMESPACE=${PFID} envsubst < ${BINDIR}/../share/asset-move.yaml.tpl | kubectl -n ${PFID} delete -f -


echo "#########################################################################"
echo "- Upgrade the instance by using the same commands as the upgrader hook"
POD_MYSQL=$(kubectl get pods --namespace=${PFID} -l component=mysql | awk '/mysql/ {print $1}')
POD_DAEMON=$(kubectl get pods --no-headers --namespace=${PFID} -l component=pim-daemon-webhook-consumer-process | awk 'NR==1{print $1}')
# echo "Fix Onboarder tables creation"
# kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/console akeneo:onboarder:setup-database --no-interaction'
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/indices?format=json&pretty'"
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/console doctrine:migrations:sync-metadata-storage --no-interaction --quiet'
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/console doctrine:migration:migrate -vvv --allow-no-migration --no-interaction'
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/console akeneo:elasticsearch:update-total-fields-limit -vvv --no-interaction'
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/indices?format=json&pretty'"


echo "#########################################################################"
echo "- Anonymize users and create admin user"
kubectl exec -it -n ${PFID} ${POD_MYSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE akeneo_connectivity_connection SET webhook_url = NULL, webhook_enabled = 0;"'
kubectl exec -it -n ${PFID} ${POD_MYSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE oro_user SET email = LOWER(CONCAT(SUBSTRING(CONCAT(\"support+clone_\", REPLACE(username,\"@\",\"_\")), 1, 64), \"@akeneo.com\"));"'
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/console pim:user:create '${PIM_USER}' '${PIM_PASSWORD}' product-team@akeneo.com admin1 admin2 en_US --admin -n || echo "WARN: User '${PIM_USER}' exists"'
# Workarround to be sure to have a admin user
SQL_COMMAND=$(cat ${BINDIR}/add_user_to_all_groups.sql)
# _ "${SQL_COMMAND}" -> allow to pass parameter
kubectl exec -it -n ${PFID} ${POD_MYSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "$@"' _ "${SQL_COMMAND}"


echo "#########################################################################"
echo "- Check ES indexation"
(kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/es_sync_checker --only-count') || true


echo "#########################################################################"
echo "- Take an ES snapshot"
ES_SNAPSHOT_NAME_JOB="elasticsearch-snapshotter"
ES_SNAPSHOT_REPOSITORY="pim_gcs_repository"
kubectl create job ${ES_SNAPSHOT_NAME_JOB}-manually --from=cronjob/${ES_SNAPSHOT_NAME_JOB} --namespace=${PFID}
# Get the ES snapshot name
echo "curl 'elasticsearch-client:9200/_snapshot/${ES_SNAPSHOT_REPOSITORY}/_all?format=json&pretty'"
SNAPSHOT_LIST=$(kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_snapshot/${ES_SNAPSHOT_REPOSITORY}/_all?format=json&pretty'")

CONTINUE=true
RETRY_LEFT=10
while ${CONTINUE}; do
  SNAPSHOT_LIST=$(kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_snapshot/${ES_SNAPSHOT_REPOSITORY}/_all?format=json&pretty'")
  STATE=$(echo ${SNAPSHOT_LIST} | jq --raw-output '.snapshots[-1].state')
  if [[ "${STATE}" == "SUCCESS" ]]; then
    CONTINUE=false
    break
  else
    sleep 30s
  fi
  if [[ "${RETRY_LEFT}" -eq 0 ]]; then
    CONTINUE=false
    break
  fi
  RETRY_LEFT=$((RETRY_LEFT-1))
done
# Get the last ES snapshot (snapshot are sort by startDate)
ES_SNAPSHOT=$(echo ${SNAPSHOT_LIST} | jq --raw-output '.snapshots[-1].snapshot')


echo "#########################################################################"
echo "- Get back SRNT chart"
rm -rf ${PED_DIR}/deployments/terraform
BOTO_CONFIG=/dev/null gsutil -m cp -r gs://akecld-terraform-modules/serenity-edition-dev/${PED_TAG}/deployments/terraform ${PED_DIR}/deployments/
# Remove hook_upgrade_pim.yaml file, cannot be use right now as ES is still mono node
rm -rf ${PED_DIR}/deployments/terraform/pim/templates/hook_upgrade_pim.yaml
# Copy back the initial generated values.yaml file
cp /tmp/values.yaml ${DESTINATION_PATH}/values.yaml


echo "#########################################################################"
echo "- Terraform init and apply"
cd ${DESTINATION_PATH}
terraform init
terraform apply -input=false -auto-approve


echo "#########################################################################"
echo "- Check indices before restore"
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/indices?format=json&pretty'"


echo "#########################################################################"
echo "- Restore the ES snapshot"
echo "curl -X POST 'elasticsearch-client:9200/_snapshot/${ES_SNAPSHOT_REPOSITORY}/${ES_SNAPSHOT}/_restore' -H 'Content-Type: application/json' -d' {\"indices\": \"*,-.*\"}'"
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl -X POST 'elasticsearch-client:9200/_snapshot/${ES_SNAPSHOT_REPOSITORY}/${ES_SNAPSHOT}/_restore' -H 'Content-Type: application/json' -d' {\"indices\": \"*,-.*\"}'"
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/indices?format=json&pretty'"
# Wait untill the restore is finished
CONTINUE=true
RETRY_LEFT=10
while ${CONTINUE}; do
  kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/recovery?format=json&pretty'" > /tmp/snapshot_recovery.json
  STATE=$(cat /tmp/snapshot_recovery.json | jq --raw-output '(.[] | select(.type=="snapshot") | .stage)' | grep -v done) || true
  if [[ "${STATE}" == "" ]]; then
    rm /tmp/snapshot_recovery.json
    CONTINUE=false
    break
  else
    sleep 30s
  fi
  if [[ "${RETRY_LEFT}" -eq 0 ]]; then
    CONTINUE=false
    break
  fi
  RETRY_LEFT=$((RETRY_LEFT-1))
done
kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/indices?format=json&pretty'"


echo "#########################################################################"
echo "- Check ES indexation"
(kubectl exec -it -n ${PFID} ${POD_DAEMON} -- /bin/bash -c 'bin/es_sync_checker --only-count') || true


echo "#########################################################################"
echo "- Upgrade config files"
yq d -i ${DESTINATION_PATH}/values.yaml pim.hook
# To be able to remove the ressources
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/serenity-edition-dev/${PED_TAG}//deployments/terraform"
yq w -j -P -i ${DESTINATION_PATH}/main.tf.json 'module.pim-monitoring.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/serenity-edition-dev/${PED_TAG}//deployments/terraform/monitoring"
