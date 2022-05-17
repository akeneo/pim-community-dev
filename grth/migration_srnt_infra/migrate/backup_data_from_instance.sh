#!/bin/bash
set -euox pipefail

if [[ -z "${SOURCE_PFID:-}" ]]; then
    echo "ERROR: environment variable SOURCE_PFID is not set." >&2
    exit 1
fi

readonly MYSQL_SOURCE_POD=$(kubectl get pods --namespace=${SOURCE_PFID} -l component=mysql | awk '/mysql/ {print $1}')

echo "Removing backup data from Bucket if it exists"
gsutil ls gs://mig-ge-to-srnt/${SOURCE_PFID}/backup && gsutil rm -r gs://mig-ge-to-srnt/${SOURCE_PFID}/backup || echo "Nothing to delete."

echo "Generate Customer SQL dump"
kubectl exec -i --namespace ${SOURCE_PFID} ${MYSQL_SOURCE_POD} -- /bin/bash -c 'mysqldump -u root -p$(cat /mysql_temp/root_password.txt) akeneo_pim | gzip' | gsutil cp - gs://mig-ge-to-srnt/${SOURCE_PFID}/backup/mysql/dump_customer_data_ge.sql.gz

echo "Copy bucket"
gsutil -q -m rsync -r gs://${SOURCE_PFID} gs://mig-ge-to-srnt/${SOURCE_PFID}/backup/bucket
