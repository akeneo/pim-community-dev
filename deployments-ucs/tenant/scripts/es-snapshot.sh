#!/bin/sh
set -euo pipefail

echo "Creating snapshot"
curl -s -i -XPUT "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}/%3Csnapshot-%7Bnow%7Byyyy-MM-dd_HH-mm-ss%7CEurope%2FParis%7D%7D%3E" | grep "200 OK"

# List snapshots to delete
export ES_SNAPSHOTS=$(curl -s -XGET "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}/_all" | jq -r ".snapshots[:-${SNAPSHOT_RETENTION}][].snapshot")

# Loop over the results and delete each snapshot
for SNAPSHOT in ${ES_SNAPSHOTS}
do
  echo "Deleting snapshot: ${SNAPSHOT}"
  curl -s -i -XDELETE "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}/${SNAPSHOT}" | grep "200 OK"
done
