#!/bin/sh
set -euo pipefail

echo "- Configure the node with the correct repository"
curl -f -X POST "${APP_INDEX_HOSTS}/_nodes/reload_secure_settings?pretty"
curl -X PUT "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}?pretty" \
      -H 'Content-Type: application/json' \
      -d @- << EOF
{
  "type": "gcs",
    "settings": {
       "bucket": "${BUCKET_NAME}",
       "base_path": "${SNAPSHOTS_FOLDER}",
       "readonly": "true",
       "client": "default"
    }
}
EOF

echo "- Get the last snapshot"
SNAPSHOT_LIST=$(curl "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}/_all?format=json&pretty")
# Get the last ES snapshot (snapshot are sort by startDate)
ES_SNAPSHOT=$(echo ${SNAPSHOT_LIST} | jq --raw-output '.snapshots[-1].snapshot')


echo "- Restore the ES snapshot"
echo "curl -X POST '${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}/${ES_SNAPSHOT}/_restore' -H 'Content-Type: application/json' -d' {\"indices\": \"*,-.*\"}'"

curl -X POST "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}/${ES_SNAPSHOT}/_restore" -H 'Content-Type: application/json' -d' {"indices": "*,-.*"}'
curl "${APP_INDEX_HOSTS}/_cat/indices?format=json&pretty"

# Wait untill the restore started
CONTINUE=true
while ${CONTINUE}; do
  echo "Check snapshot recovery started"
  curl "${APP_INDEX_HOSTS}/_cat/recovery?format=json&pretty"
  curl "${APP_INDEX_HOSTS}/_cat/recovery?format=json&pretty" > /tmp/snapshot_recovery.json
  STATE=$(cat /tmp/snapshot_recovery.json | jq --raw-output '(.[] | select(.type=="snapshot") | .stage)') || true
  echo "STATE: ${STATE}"
  if [[ "${STATE}" == "" ]]; then
    echo "Restoration not started"
    sleep 10s
  else
    rm /tmp/snapshot_recovery.json
    CONTINUE=false
    echo "Restoration started"
    break
  fi
done
curl "${APP_INDEX_HOSTS}/_cat/indices?format=json&pretty"

# Wait untill the restore is finished
CONTINUE=true
RETRY_LEFT=10
while ${CONTINUE}; do
  echo "Check snapshot recovery"
  curl "${APP_INDEX_HOSTS}/_cat/recovery?format=json&pretty"
  curl "${APP_INDEX_HOSTS}/_cat/recovery?format=json&pretty" > /tmp/snapshot_recovery.json
  STATE=$(cat /tmp/snapshot_recovery.json | jq --raw-output '(.[] | select(.type=="snapshot") | .stage)' | grep -v done) || true
  echo "STATE: ${STATE}"
  if [[ "${STATE}" == "" ]]; then
    rm /tmp/snapshot_recovery.json
    CONTINUE=false
    echo "Restoration finished"
    break
  else
    echo "Restoration in progress"
    sleep 30s
  fi
  if [[ "${RETRY_LEFT}" -eq 0 ]]; then
    CONTINUE=false
    echo "Restoration not finished, but waiting for to long"
    break
  fi
  RETRY_LEFT=$((RETRY_LEFT-1))
done
curl "${APP_INDEX_HOSTS}/_cat/indices?format=json&pretty"
