#!/bin/sh
set -ex

cd /tmp
zip ${SOURCE_CODE_ZIP} /ucs-function-cron-transformer/*.js /ucs-function-cron-transformer/*.json
SA_TOKEN=$(curl -v -H "Metadata-Flavor: Google" http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token | jq '.access_token')
curl -H "Authorization: OAuth ${SA_TOKEN}" -X POST -v --data-binary @${SOURCE_CODE_ZIP} "https://storage.googleapis.com/upload/storage/v1/b/"${BUCKET_NAME}"/o?uploadType=media&name="${SOURCE_CODE_ZIP}
