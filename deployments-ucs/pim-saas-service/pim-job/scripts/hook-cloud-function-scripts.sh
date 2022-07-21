#!/bin/sh
cd /tmp
tar -cvf  ucs-function-cron-transformer.zip /ucs-function-cron-transformer
curl -X POST  -v  --data-binary @ucs-function-cron-transformer.zip  \
      "https://storage.googleapis.com/upload/storage/v1/b/srnt-ucs-pim-cron/o?uploadType=media&name=ucs-function-cron-transformer.zip"