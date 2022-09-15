#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
#NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pimci-pr"
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-(pim(ci|up)|ge2ee-last)"

TF_STATE_LIST=$(gsutil ls gs://akecld-terraform-dev/saas/akecld-saas-dev/**/default.tfstate)

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

echo "Found $( echo ${TF_STATE_LIST} | wc -l) state(s)"
LOG_PATH=$(mktemp)
echo "Log path : ${LOG_PATH}"

COUNT=0
REASON_NS_EXIST=0
for TF_STATE_PATH in $(echo $TF_STATE_LIST); do
    INSTANCE_NAME=$(basename $(dirname $TF_STATE_PATH))
    NS_EXIST=$(echo $KUBE_NS_LIST | grep -w ${INSTANCE_NAME} | wc -l)
    if [[ ${NS_EXIST} -eq 1 ]]; then
#        echo "  Existing namespace : DELETION SKIPPED"
        ((REASON_NS_EXIST++))
        continue
    fi

    if [[ ${INSTANCE_NAME} =~ $NAMESPACE_REGEX_FILTER ]] ; then
        echo "Instance :"
        echo "  Name : ${INSTANCE_NAME}"
        echo "  GS path : ${TF_STATE_PATH}"

        CREATION_DATE=$(gsutil stat ${TF_STATE_PATH} | grep "Creation" | grep -o ":.*" | grep -o "[A-Z].*")
        DAY_DIFF=$(( ($(date +%s) - $(date -d "${CREATION_DATE}" +%s)) / (60*60*24) ))
        echo "  Creation date : ${CREATION_DATE} (${DAY_DIFF}d)"

        if [[ 90 -gt ${DAY_DIFF} ]]; then
          echo "  Tf state newer than 90 days : DELETION SKIPPED"
          continue
        fi

        echo "  Command debug"
        echo "      #gsutil -m rm -r $(dirname ${TF_STATE_PATH})"
        echo "      gsutil mv ${TF_STATE_PATH} $(dirname ${TF_STATE_PATH})/state.bckup"

        echo ${TF_STATE_PATH} >> ${LOG_PATH}
        gsutil mv ${TF_STATE_PATH} $(dirname ${TF_STATE_PATH})/state.bckup
#        gsutil -m rm -r $(dirname ${TF_STATE_PATH})
        ((COUNT++))
    fi
done

echo "Log path : ${LOG_PATH}"
echo "${COUNT} state(s) removed"
