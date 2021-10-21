#!/usr/bin/env bash 
set -o pipefail
set -x

if [[ "${SOURCE_PFID}" == "" ]]; then
      echo "ERR : You must choose a prod source instance"
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

BINDIR=$(dirname $(readlink -f $0))
PED_DIR="${BINDIR}/../.."


PFID="srnt-"${INSTANCE_NAME}
SOURCE_INSTANCE_NAME=$(echo ${SOURCE_PFID}| cut -c 6-)
DESTINATION_PATH=${DESTINATION_PATH:-${PED_DIR}/deployments/instances/${PFID}}
TARGET_PAPO_PROJECT_CODE=${TARGET_PAPO_PROJECT_CODE:-"NOT_ON_PAPO_${PFID}"}
DESTINATION_GOOGLE_CLUSTER_ZONE="europe-west3-a"
SOURCE_GOOGLE_PROJECT_ID="akecld-saas-prod"
DESTINATION_GOOGLE_PROJECT_ID="akecld-saas-dev"
TARGET_DNS_FQDN="${INSTANCE_NAME}.dev.cloud.akeneo.com."


main(){
{
      prepare_kubectl_context
      create_flex_chart
      create_data_disks
} ||{
      exit 1
}
{
      generate_kubeconfig
      patch_data_disk
      deploy_instance
      post_instance_fix
}||{
      echo "big clean"
      big_clean_onerror
      clean_useless-files
      exit 1
}
      launch_migrations
      clean_useless-files
}



prepare_kubectl_context()
{
      echo "#########################################################################"
      echo "- Prepare kubectl context -"
      echo ${GCLOUD_SERVICE_KEY} | gcloud auth activate-service-account --key-file=-
      gcloud config set project ${DESTINATION_GOOGLE_PROJECT_ID}
      gcloud config set compute/zone ${DESTINATION_GOOGLE_CLUSTER_ZONE}

      gcloud auth configure-docker --quiet
      gcloud container clusters  get-credentials --project=akecld-saas-prod --zone=europe-west2-b europe-west2-b
      gcloud container clusters  get-credentials --project=${DESTINATION_GOOGLE_PROJECT_ID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} ${DESTINATION_GOOGLE_CLUSTER_ZONE}

      echo ${GCLOUD_SERVICE_KEY} > ${HOME}/gcloud-service-key.json
      export GOOGLE_APPLICATION_CREDENTIALS="${HOME}/gcloud-service-key.json"
}

create_flex_chart() {
      echo "#########################################################################"
      echo "- Build a Flex into Serenity helm chart on the Fly!"

      cp -r ${PED_DIR}/deployments/terraform ${DESTINATION_PATH}
      rm ${DESTINATION_PATH}/terraform/pim/requirements.lock
      yq d -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==elasticsearch)"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies[+].name" "flex-es"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==flex-es).repository" "file://${PED_DIR}/deployments/share/flex-es/"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==flex-es).alias" "elasticsearch"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==flex-es).version" "0.0.0"

      yq d -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==mysql)"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies[+].name" "flex-mysql"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==flex-mysql).repository" "gs://akeneo-charts/"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==flex-mysql).alias" "mysql"
      yq w -i  ${DESTINATION_PATH}/terraform/pim/requirements.yaml "dependencies.(name==flex-mysql).version" "1.0.0"

      yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.installPim.enabled false
      yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.addAdmin.enabled false
      yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.upgradeES.enabled false
      rm ${DESTINATION_PATH}/terraform/pim/hook_upgrade_pim.yaml

      yq d -i  ${DESTINATION_PATH}/terraform/pim/values.yaml "elasticsearch"
      yq d -i  ${DESTINATION_PATH}/values.yaml "elasticsearch"


      yq w -i  ${DESTINATION_PATH}/values.yaml "mysql.mysql.resetPassword" "true"
      yq w -i  ${DESTINATION_PATH}/values.yaml "mysql.mysql.mountDiskPath" "/data"
      yq w -i  ${DESTINATION_PATH}/values.yaml "mysql.mysql.dataDiskPath" "/data/var/lib/mysql"
      yq w -i  ${DESTINATION_PATH}/values.yaml "mysql.image.mysql.tag" --tag '!!str' 8.0
}

create_data_disks() {
      jq  --arg path "./terraform" '.module.pim.source = $path' ${DESTINATION_PATH}/main.tf.json > ${DESTINATION_PATH}/main.tf.json.2
      jq 'del(.module."pim-monitoring")' ${DESTINATION_PATH}/main.tf.json.2 > ${DESTINATION_PATH}/main.tf.json && rm ${DESTINATION_PATH}/main.tf.json.2

      read -r -a FLEX_INFO <<< $(kubectl --context=gke_akecld-saas-prod_europe-west2-b_europe-west2-b get backups --namespace=paas-backup -l product_type=flexibility_prod -l instance_dns_record=${SOURCE_PFID}   -o jsonpath='{range .items[*]}{.metadata.labels.instance_dns_record}{" "}{.metadata.labels.gcloud_project_id}{" "}{.spec.zone}{"\n"}{end}')

      FLEX_SOURCE_PROJECT=${FLEX_INFO[1]}
      if [[ $FLEX_SOURCE_PROJECT == "" ]]; then
            echo "${SOURCE_PFID} is not trouvable"
            exit 9
      fi
      echo "#########################################################################"
      echo "- Get last snapshot selflink"
      cd ${PED_DIR}
      SELFLINK_FLEX=$(gcloud --project="${FLEX_SOURCE_PROJECT}" compute snapshots list --filter="labels.backup-name=${SOURCE_PFID}" --limit=1 --sort-by="~creationTimestamp" --uri)

      FLEX_SIZE=$(gcloud compute snapshots describe $SELFLINK_FLEX --format=json | jq -r '.diskSizeGb')
      echo "$SELFLINK_FLEX / $FLEX_SIZE"

      echo "- Create disk ES disk (delete before if existing)"
      ES_DISK_NAME=${PFID}-es

      if [[ $( gcloud compute disks describe ${ES_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
            gcloud compute disks delete ${ES_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
            sleep 10
      fi
      DISKES=$(gcloud compute disks create ${ES_DISK_NAME} --source-snapshot=${SELFLINK_FLEX} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
      if [[ $DISKES == "" ]]; then
            echo "ES duplicate disk does not exist, exiting"
            return 1
      fi
      echo "ES duplicate disk has been created : $DISKES"


      echo "- Create disk Mysql disk (delete before if existing)"
      MYSQL_DISK_NAME=${PFID}-mysql
      if [[ $( gcloud compute disks describe ${MYSQL_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
            gcloud compute disks delete ${MYSQL_DISK_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
            sleep 10
      fi
      DISKMYSQL=$(gcloud compute disks create ${MYSQL_DISK_NAME} --source-snapshot=${SELFLINK_FLEX} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
      if [[ $DISKMYSQL == "" ]]; then
            echo "Mysql duplicate disk does not exist, exiting"
            return 1
      fi
      echo "Mysql duplicate disk has been created : $DISKMYSQL"

      yq w -i  ${DESTINATION_PATH}/values.yaml "elasticsearch.single.persistentDisk.size" "$FLEX_SIZE"
      yq w -i  ${DESTINATION_PATH}/values.yaml "elasticsearch.single.persistentDisk.name" "${ES_DISK_NAME}"
      yq w -i  ${DESTINATION_PATH}/values.yaml "elasticsearch.image.es.tag" "7.14.1"
      yq w -i  ${DESTINATION_PATH}/values.yaml "elasticsearch.common.service.name" "elasticsearch-client"
      yq w -i  ${DESTINATION_PATH}/values.yaml "elasticsearch.fullnameOverride" "elasticsearch"

      jq  --arg size "${FLEX_SIZE}" '.module.pim.mysql_disk_size = $size' ${DESTINATION_PATH}/main.tf.json > ${DESTINATION_PATH}/main.tf.json.2
      jq  --arg snap "${SELFLINK_FLEX}" '.module.pim.mysql_source_snapshot = $snap'  ${DESTINATION_PATH}/main.tf.json.2 > ${DESTINATION_PATH}/main.tf.json.3
      jq  --arg name "${MYSQL_DISK_NAME}" '.module.pim.mysql_disk_name = $name'  ${DESTINATION_PATH}/main.tf.json.3 > ${DESTINATION_PATH}/main.tf.json && rm ${DESTINATION_PATH}/main.tf.json.*

}

generate_kubeconfig (){
      cd ${DESTINATION_PATH}
      rm -rf terraform/pim/charts
      echo "#########################################################################"
      echo "- Generate kubeconfig -"

      terraform init
      terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
}

patch_data_disk(){
      echo "#########################################################################"
      echo "- Prepare Mysql and ES disk"
      export KUBECONFIG=.kubeconfig

      kubectl describe namespace ${PFID} || kubectl create namespace ${PFID}
      DISK_INSTANCE_NAME=${ES_DISK_NAME} SOURCE_PFID=${SOURCE_PFID}  NAMESPACE=${PFID} envsubst < ${BINDIR}/../share/es-data-move.yaml.tpl | kubectl  -n ${PFID} apply -f -
      DISK_INSTANCE_NAME=${MYSQL_DISK_NAME} SOURCE_PFID=${SOURCE_PFID} NAMESPACE=${PFID}  envsubst < ${BINDIR}/../share/mysql-data-move.yaml.tpl | kubectl  -n ${PFID} apply -f -

      kubectl -n ${PFID} wait --for=condition=complete --timeout=3m job/es-data-move || true
      kubectl -n ${PFID} wait --for=condition=complete --timeout=3m job/mysql-data-move || true
      DISK_INSTANCE_NAME=${ES_DISK_NAME} SOURCE_PFID=${SOURCE_PFID}  NAMESPACE=${PFID} envsubst < ${BINDIR}/../share/es-data-move.yaml.tpl | kubectl  -n ${PFID} delete -f -
      DISK_INSTANCE_NAME=${MYSQL_DISK_NAME} SOURCE_PFID=${SOURCE_PFID} NAMESPACE=${PFID}  envsubst < ${BINDIR}/../share/mysql-data-move.yaml.tpl | kubectl  -n ${PFID} delete -f -
      terraform import module.pim.google_compute_disk.mysql-disk projects/${DESTINATION_GOOGLE_PROJECT_ID}/zones/${DESTINATION_GOOGLE_CLUSTER_ZONE}/disks/${MYSQL_DISK_NAME}
}

deploy_instance(){
      echo "#########################################################################"
      echo "- Create Serenity instance"
      terraform apply -input=false -auto-approve
}

post_instance_fix ()
{
      echo "#########################################################################"
      echo "- Create disk Migration init (delete before if existing)"
      MIGRATION_NAME=${PFID}-migration

      if [[ $( gcloud compute disks describe ${MIGRATION_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
            gcloud compute disks delete ${MIGRATION_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
            sleep 10
      fi
      MIGDOC=$(gcloud compute disks create ${MIGRATION_NAME} --source-snapshot=${SELFLINK_FLEX} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
      if [[ $MIGDOC == "" ]]; then
            echo "ES duplicate disk does not exist, exiting"
            exit 9
      fi
      echo "Migration duplicate disk has been created : $MIGDOC"

      DISK_INSTANCE_NAME=${MIGRATION_NAME} NAMESPACE=${PFID} envsubst < ../../share/migration-fixer.tpl| kubectl -n ${PFID} apply -f -
      kubectl -n ${PFID} wait --for=condition=complete --timeout=3m job/migration-fixer || true

      DISK_INSTANCE_NAME=${MIGRATION_NAME} NAMESPACE=${PFID} envsubst < ../../share/migration-fixer.tpl| kubectl -n ${PFID} delete -f -

      if [[ $( gcloud compute disks describe ${MIGRATION_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
            gcloud compute disks delete ${MIGRATION_NAME} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
            sleep 10
      fi

      echo "Fix Onboarder tables creation"
      PODDAEMON=$(kubectl get pods --namespace=${PFID} -l component=pim-daemon-default -o jsonpath='{.items[0].metadata.name}')
      kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console akeneo:onboarder:setup-database --no-interaction'
}

launch_migrations(){
      kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console doctrine:migration:migrate -vvv --allow-no-migration --no-interaction'
      kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c "curl 'elasticsearch-client:9200/_cat/indices?format=json&pretty'"
}

clean_useless-files()
{
      rm -rf ${DESTINATION_PATH}/.terraform
}

big_clean_onerror()
{
      cd ${PED_DIR}
      INSTANCE_NAME=${INSTANCE_NAME} make -C deployments/ delete_clone_flexibility
}

main
