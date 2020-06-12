#!/bin/bash -eo pipefail

if [[ ${SOURCE_PFID} == "" ]]; then
        echo "ERR : You must choose a prod source instance"
	exit 9
fi
if [[ ${SOURCE_PED_TAG} == "" ]]; then
        echo "ERR : You must choose a prod source version for PED deployment"
        exit 9
fi
if [[ ${INSTANCE_NAME} == "" ]]; then
        echo "ERR : You must choose an instance name for the duplicate instance"
        exit 9
fi

if [[ $CI == "true" ]]; then
        PED_DIR="/root/project"
else
        BINDIR=$(dirname $(readlink -f $0))
        PED_DIR="${BINDIR}/../../"
fi

#
PFID="srnt-"${INSTANCE_NAME}
DESTINATION_PATH=${PED_DIR}/deployments/instances/${PFID}
DESTINATION_GOOGLE_CLUSTER_ZONE="europe-west3-a"
SOURCE_GOOGLE_PROJECT_ID="akecld-saas-prod"
DESTINATION_GOOGLE_PROJECT_ID="akecld-saas-dev"
#

echo "1- create config files"
cd ${PED_DIR}; INSTANCE_NAME=${INSTANCE_NAME}  IMAGE_TAG=${SOURCE_PED_TAG}  make create-ci-release-files
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.installPim.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.addAdmin.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml pim.hook.upgradeES.enabled false
yq w -i ${DESTINATION_PATH}/values.yaml mysql.common.persistentDisks[0] ${PFID}
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.resetPassword true
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.userPassword test
yq w -i ${DESTINATION_PATH}/values.yaml mysql.mysql.rootPassword test
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.email "findUserInDatabase"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.login "findUserInDatabase"
yq w -i ${DESTINATION_PATH}/values.yaml pim.defaultAdminUser.password "changePasswdInDatabase"
sed -i '/monitoring/s#/root/project/deployments/terraform/monitoring#git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform/monitoring?ref='"${SOURCE_PED_TAG}"'#' ${DESTINATION_PATH}/main.tf
sed -i 's#/root/project/deployments/terraform#git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref='"${SOURCE_PED_TAG}"'#' ${DESTINATION_PATH}/main.tf
cat ${DESTINATION_PATH}/main.tf

if [[ $CI != "true" ]]; then
	echo "Check your configurations files if needed : ${DESTINATION_PATH}"
	echo "Enter to continue, ^C to exit"
	read
fi

echo "2- get last snapshot selflink"
SELFLINKMYSQL=$(gcloud --project=${SOURCE_GOOGLE_PROJECT_ID} compute snapshots list --filter="labels.backup-ns=${SOURCE_PFID}" --filter="labels.pvc_name=data-mysql-server-0" --limit=1 --sort-by="~creationTimestamp" --format=json | jq '.[0]["selfLink"]'|sed 's/"//g')
echo $SELFLINKMYSQL

echo "3- create disk (delete before if existing)"
if [[ $( gcloud compute disks describe ${PFID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet >/dev/null 2>&1 && echo "diskExists" ) == "diskExists" ]]; then
	gcloud compute disks delete ${PFID} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --quiet
	sleep 10
fi
DISKMYSQL=$(gcloud compute disks create ${PFID} --source-snapshot=${SELFLINKMYSQL} --zone=${DESTINATION_GOOGLE_CLUSTER_ZONE} --project=${DESTINATION_GOOGLE_PROJECT_ID} --type=pd-ssd --format=json)
if [[ $DISKMYSQL == "" ]]; then
	echo "Mysql duplicate disk not exists, exiting"
	exit 9
fi
echo "Mysql duplicate disk has been created : $DISKMYSQL"

echo "4- run terraform & helm."
cd ${PED_DIR}; TF_INPUT_FALSE="-input=false" TF_AUTO_APPROVE="-auto-approve" INSTANCE_NAME=${INSTANCE_NAME}  IMAGE_TAG=${SOURCE_PED_TAG}  make deploy

#echo "5a- Duplicate prod bucket in background, logfile : ${PED_DIR}/duplicate_bucket.log"
#cd ${PED_DIR}; nohup gsutil -m rsync -r gs://${SOURCE_PFID} gs://${PFID} > ${PED_DIR}/duplicate_bucket.log 2>&1 &

echo "5b- populate ES"
PODDAEMON=$(kubectl get pods  --namespace=${PFID}|grep pim-daemon-default|head -n 1|awk '{print $1}')
kubectl exec -it -n ${PFID} ${PODDAEMON} -- /bin/bash -c 'bin/console akeneo:elasticsearch:reset-indexes --env=prod --quiet; bin/console pim:product:index --all --env=prod; bin/console pim:product-model:index --all --env=prod;'

echo "6- anonymise"
PODSQL=$(kubectl get pods  --namespace=${PFID}|grep mysql|head -n 1|awk '{print $1}')
kubectl exec -it -n ${PFID} ${PODSQL} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim -e "UPDATE oro_user SET email = LOWER(CONCAT(SUBSTRING(CONCAT(\"support+clone_\", username), 1, 64), \"@akeneo.com\"));"'
