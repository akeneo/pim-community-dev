#!/bin/sh
set -ex

cd /tmp
cp /ucs-function-cron-transformer/*.js .
cp /ucs-function-cron-transformer/*.json .
zip ${SOURCE_CODE_ZIP} *.js *.json
SA_TOKEN=$(curl -v -H "Metadata-Flavor: Google" http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token | jq '.access_token')
curl -f -H "Authorization: OAuth ${SA_TOKEN}" -H "Content-Type: application/zip" -X POST -v --data-binary @${SOURCE_CODE_ZIP} "https://storage.googleapis.com/upload/storage/v1/b/"${BUCKET_NAME}"/o?uploadType=media&name="${SOURCE_CODE_ZIP}
