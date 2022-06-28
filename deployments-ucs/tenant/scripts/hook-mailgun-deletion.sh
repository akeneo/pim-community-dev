#!/bin/sh
set -euo pipefail

if [[ "${MAILGUN_DOMAIN}" == "" ]]; then
      echo "ERR : You must specify the mailgun domain to use"
      exit 22
fi
if [ "${MAILGUN_API_KEY}" == "" ]; then
      echo "ERR : You must specify the mailgun API key"
      exit 22
fi
if [[ "${MAILGUN_LOGIN}" == "" ]]; then
      echo "ERR : You must specify the mailgun login to create"
      exit 22
fi

RESULT_FILE=/tmp/curl_mailgun_deletion_response.txt

http_response=$(curl -s -X DELETE https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials/${MAILGUN_LOGIN} \
        -o ${RESULT_FILE} \
        -w "%{http_code}" \
        --user "api:${MAILGUN_API_KEY}" \
        --retry 5 \
        --retry-delay 5 \
        --retry-max-time 40 )
if [ "${http_response}" != "200" ]; then
    echo "!!! ERROR - Mailgun credentials destroy failed - http_response: ${http_response} !!!"
    cat ${RESULT_FILE}
    rm ${RESULT_FILE}
else
    echo "Mailgun credentials deletion is OK - http_response: ${http_response} "
    cat ${RESULT_FILE}
    rm ${RESULT_FILE}
fi
