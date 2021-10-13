#!/bin/bash

LIMIT=1000
ROUND=0
CONTINUE=true
OUTPUT_FILE=response_curl.json
SECONDS_PER_DAY=86400
MAILGUN_DOMAIN=mg.cloud.akeneo.com
EMAIL_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)-[0-9a-zA-Z\-]+-akecld-saas-dev@.*"

while ${CONTINUE}; do
  SKIP=$(( LIMIT*ROUND ))
  curl -s -G https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials \
      --user api:${MAILGUN_API_KEY} \
      -o ${OUTPUT_FILE} \
      -d limit=${LIMIT} \
      -d skip=${SKIP}
  if [[ $(cat ${OUTPUT_FILE} | jq -r '.items') == "[]" ]]; then
      CONTINUE=false
      break
  else
      LOGIN_LIST=$(cat ${OUTPUT_FILE} | jq -r '.items[].login')
      for LOGIN in ${LOGIN_LIST}; do

          if [[ ${LOGIN} =~ ${EMAIL_REGEX_FILTER} ]] ; then
              CREATION_DATE=$(cat ${OUTPUT_FILE} | jq -r --arg LOGIN "$LOGIN" '.items[] | select(.login == $LOGIN) | .created_at')
              CREATION_DATE_TS=$(date -u -d "${CREATION_DATE}" '+%s')
              CURRENT_TS=$(date -u '+%s')
              DIFF_TS="$((${CURRENT_TS}-${CREATION_DATE_TS}))"
              DIFF_DAY="$((${DIFF_TS}/${SECONDS_PER_DAY}))"

              if [[ $DIFF_DAY -ge 1 ]]; then
                  curl -s -X DELETE https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials/${LOGIN} \
                      --user api:${MAILGUN_API_KEY}
              fi
          fi
      done
  fi
  ROUND=$((ROUND+1))
done
