#!/bin/sh
set -ex

cd /tmp
tar -cvf ${SOURCE_CODE_ZIP} /ucs-function-cron-transformer
curl -X POST -v --data-binary @${SOURCE_CODE_ZIP} "https://storage.googleapis.com/upload/storage/v1/b/"${BUCKET_NAME}"/o?uploadType=media&name="${SOURCE_CODE_ZIP}
