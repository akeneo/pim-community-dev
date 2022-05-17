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

readonly MYSQL_TARGET_POD=$(kubectl get pods --namespace=${TARGET_PFID} -l component=mysql | awk '/mysql/ {print $1}')

echo "Load SQL dump"
kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -e "drop database akeneo_pim; create database akeneo_pim"'
gsutil cat gs://mig-ge-to-srnt/${SOURCE_PFID}/backup/mysql/dump_customer_data_ge.sql.gz | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -- /bin/bash -c 'gunzip | mysql -u root -p$(cat /mysql_temp/root_password.txt) akeneo_pim'

echo "Add missing tables"
gsutil cat gs://mig-ge-to-srnt/dump_schema_ge_to_ee.sql | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim '

echo "Add missing jobs"
gsutil cat gs://mig-ge-to-srnt/dump_data_job_instance_ee.sql | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim '

echo "Add right for jobs, categories, locales and attributes to User Group 'All'"
gsutil cat gs://mig-ge-to-srnt/add_missing_permissions.sql | kubectl exec -i --namespace ${TARGET_PFID} ${MYSQL_TARGET_POD} -- /bin/bash -c 'mysql -u root -p$(cat /mysql_temp/root_password.txt) -D akeneo_pim '

echo "Add all missing doctrine migrations"
readonly FPM_TARGET_POD=$(kubectl get pods --no-headers --namespace=${TARGET_PFID} -l component=pim-web | awk 'NR==1{print $1}')
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- /bin/bash -c 'bin/console doctrine:migration:version --add --all --no-interaction'

#echo "Launch DQI calculation into the queue"
#readonly FPM_TARGET_POD=$(kubectl get pods --no-headers --namespace=${TARGET_PFID} -l component=pim-web | awk 'NR==1{print $1}')
#kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- /bin/bash -c 'bin/console doctrine:migration:version --add --all --no-interaction'

echo "Re-index in ES"
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- bin/console akeneo:elasticsearch:reset-indexes -n
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- bin/console pim:product:index --all -n
kubectl exec -i --namespace ${TARGET_PFID} ${FPM_TARGET_POD} -- bin/console pim:product-model:index --all -n

echo "Copy bucket"
gsutil rm -r gs://${TARGET_PFID}
gsutil -q -m rsync -r gs://mig-ge-to-srnt/${SOURCE_PFID}/backup/bucket gs://${TARGET_PFID}
