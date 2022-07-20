#!/bin/sh
tar cvf ucs-function-cron-transformer.zip /tmp/ucs-function-cron-transformer
CLOUD_FUNCTION_SOURCE_ZIP=/tmp/ucs-function-cron-transformer.zip
ls -lrt /tmp/
echo ${TOKEN_OATH2}
curl -X POST --data-binary $CLOUD_FUNCTION_SOURCE_ZIP \
    -H "Content-Type: application/zip" \
    "https://storage.googleapis.com/upload/storage/v1/b/srnt-ucs-pim-cron/o?uploadType=media&name=ucs-function-cron-transformer.zip" 