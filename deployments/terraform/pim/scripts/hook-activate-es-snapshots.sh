#!/bin/bash
set -euo pipefail

curl -sf -X POST "${APP_INDEX_HOSTS}/_nodes/reload_secure_settings?pretty"
curl -sf -X PUT "${APP_INDEX_HOSTS}/_snapshot/${REPOSITORY}?pretty" \
      -H 'Content-Type: application/json' \
      -d @- << EOF

{
  "type": "gcs",
    "settings": {
       "bucket": "${BUCKET_NAME}",
       "base_path": "${SNAPSHOTS_FOLDER}"
    }
}
EOF
