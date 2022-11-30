#!/bin/sh
set -uo pipefail

if [[ "${MAILGUN_DOMAIN}" == "" ]]; then
      echo "ERR : You must specify the mailgun domain to use"
      exit 22
fi
if [[ "${MAILGUN_API_KEY}" == "" ]]; then
      echo "ERR : You must specify the mailgun API key"
      exit 22
fi
if [[ "${MAILGUN_LOGIN}" == "" ]]; then
      echo "ERR : You must specify the mailgun login to create"
      exit 22
fi
if [[ "${MAILGUN_PASSWORD}" == "" ]]; then
      echo "ERR : You must specify the mailgun password for the login"
      exit 22
fi

RESULT_FILE=/tmp/curl_mailgun_creation_response.txt

alreadyExists=$(curl -s https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials --user "api:${MAILGUN_API_KEY}" \
  --retry 5 --retry-delay 5 --retry-max-time 40 | jq -r --arg MAILGUN_LOGIN ${MAILGUN_LOGIN} \
  '[.items[].login] | any(index($MAILGUN_LOGIN))')

if [[ "${alreadyExists}" == "true" ]]; then
  exit 0
fi

http_response=$(curl -s https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials \
        -o ${RESULT_FILE} \
        -w "%{http_code}" \
        --user "api:${MAILGUN_API_KEY}" \
        -F login="${MAILGUN_LOGIN}" \
        -F password="${MAILGUN_PASSWORD}" \
        --retry 5 \
        --retry-delay 5 \
        --retry-max-time 40 )

if [ "${http_response}" != "200" ]; then
    echo "!!! ERROR - Mailgun credentials creation failed - http_response: ${http_response} !!!"
    cat ${RESULT_FILE}
    rm ${RESULT_FILE}
    exit 9
else
    echo "Mailgun credentials creation is OK - http_response: ${http_response}"
    cat ${RESULT_FILE}
    rm ${RESULT_FILE}
fi
