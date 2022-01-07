#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"
LIMIT=1000
ROUND=0
CONTINUE=true
OUTPUT_FILE=response_curl.json
SECONDS_PER_DAY=86400
MAILGUN_DOMAIN=mg.cloud.akeneo.com
EMAIL_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)-[0-9a-zA-Z\-]+-akecld-saas-dev@.*"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

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
        NAMESPACE="${LOGIN%%-akecld-saas-dev*}"

        if [[ ${NAMESPACE} =~ $NAMESPACE_REGEX_FILTER ]] ; then
          echo "--------------------------------------"
          echo "Mailgun credential : ${LOGIN}"

          NAMESPACE_EXIST=$(echo ${KUBE_NS_LIST} | grep -w ${NAMESPACE} | wc -l)
          if [ "${NAMESPACE_EXIST}" -eq 1 ]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
          fi

          CREATION_DATE=$(cat ${OUTPUT_FILE} | jq -r --arg LOGIN "${LOGIN}" '.items[] | select(.login == $LOGIN) | .created_at')
          echo "  Created at :                            ${CREATION_DATE}"
          CREATION_DATE_TS=$(date -u -d "${CREATION_DATE}" '+%s')
          CURRENT_TS=$(date -u '+%s')
          DIFF_TS="$((${CURRENT_TS}-${CREATION_DATE_TS}))"
          DIFF_DAY="$((${DIFF_TS}/${SECONDS_PER_DAY}))"

          if [[ ${DIFF_DAY} -ge 1 ]]; then
            echo "  Command debug"
            echo "      curl -s -X DELETE https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials/${LOGIN}"
            echo "              --user api:MAILGUN_API_KEY"
            curl -s -X DELETE https://api.mailgun.net/v3/domains/${MAILGUN_DOMAIN}/credentials/${LOGIN} \
                --user api:${MAILGUN_API_KEY}
          else
            echo "  Mailgun credential newer than 1 day :   DELETION SKIPPED"
            continue
          fi
        else
          echo "  Namespace doesn't match : DELETION SKIPPED"
        fi
      fi
    done
  fi
  ROUND=$((ROUND+1))
done
