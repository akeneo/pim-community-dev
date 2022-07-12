#!/bin/bash
set -euox pipefail

if [[ -z "${SOURCE_PFID:-}" ]]; then
    echo "ERROR: environment variable SOURCE_PFID is not set." >&2
    exit 1
fi

if [[ -z "${TARGET_PFID:-}" ]]; then
    echo "ERROR: environment variable TARGET_PFID is not set." >&2
    exit 1
fi

# Configure Kubeconfig via terraform target
if [ ! -r ".kubeconfig" ]; then
    terraform init
    terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
fi
export KUBECONFIG=.kubeconfig

#Need to add retry loop because Helm finish before MySQL pod readiness - Retry every 5s for 120s
RETRY_NUM=1
RETRY_EVERY=5
MYSQL_TARGET_POD=""

while [[ -z $MYSQL_TARGET_POD ]]
do
    MYSQL_TARGET_POD=$(kubectl get pods --namespace=${TARGET_PFID} -l component=mysql -o jsonpath="{.items[0].metadata.name}" --ignore-not-found)

    if [[ -z $MYSQL_TARGET_POD ]]
    then
        ((RETRY_NUM++))
        echo "WARNING: Retrieving MYSQL_TARGET_POD was not successful after ${RETRY_NUM} tries...Retry in 5s"
        sleep ${RETRY_EVERY}
    fi
    if [ ${RETRY_NUM} -eq 24 ]
    then
        echo "ERROR: Retrieve MYSQL_TARGET_POD was not successful after ${RETRY_NUM} tries"
        exit 1
    fi
done

echo "Load SQL dump"
kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -c mysql-server -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -e "drop database akeneo_pim; create database akeneo_pim"'
gsutil cat gs://mig-ge-to-srnt/${SOURCE_PFID}/backup/mysql/dump_customer_data_ge.sql.gz | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -c mysql-server -- /bin/bash -c 'gunzip | mysql -u root -p$(cat /mysql_temp/root_password.txt) akeneo_pim'

echo "Add missing tables"
gsutil cat gs://mig-ge-to-srnt/dump_schema_ge_to_ee.sql | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -c mysql-server -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim '

echo "Add missing jobs"
gsutil cat gs://mig-ge-to-srnt/dump_data_job_instance_ee.sql | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -c mysql-server -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim '

echo "Add right for jobs, categories, locales and attributes to User Group 'All'"
gsutil cat gs://mig-ge-to-srnt/add_missing_permissions.sql | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -c mysql-server -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim '

echo "Add all missing doctrine migrations"
readonly FPM_TARGET_POD=$(kubectl get pods --no-headers --namespace=${TARGET_PFID} -l component=pim-web | awk 'NR==1{print $1}')
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- /bin/bash -c 'bin/console doctrine:migration:version --add --all --no-interaction'

echo "Launch DQI calculation into the queue"
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- /bin/bash -c 'bin/console pim:data-quality-insights:initialize-growth-edition-double-score'

echo "Re-index in ES"
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- bin/console akeneo:elasticsearch:reset-indexes -n
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- bin/console pim:product:index --all -n
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- bin/console pim:product-model:index --all -n

echo "Copy bucket"
gsutil -m rm -a "gs://${TARGET_PFID}/**"
gsutil -q -m rsync -r gs://mig-ge-to-srnt/${SOURCE_PFID}/backup/bucket gs://${TARGET_PFID}
