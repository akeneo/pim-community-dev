#!/bin/sh
set -euo pipefail

echo "- Configure the node with the correct repository"
curl -sf -X POST "${APP_INDEX_HOSTS}/_nodes/reload_secure_settings?pretty"
curl -sf -X PUT "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}?pretty" \
      -H 'Content-Type: application/json' \
      -d @- << EOF
{
  "type": "gcs",
    "settings": {
       "bucket": "${BUCKET_NAME}",
       "base_path": "${SNAPSHOTS_FOLDER}",
       "client": "default"
    }
}
EOF
