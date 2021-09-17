#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
SECONDS_PER_DAY=$((24*60*60))
MIN_AGE=1
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

# List of gcloud physical disk with a description and no usage (users) in ${GOOGLE_PROJECT_ID}
BUCKET_LIST_JSON=$(gcloud alpha storage ls --json)
# BUCKET_LIST_JSON=$(gsutil ls -p ${GOOGLE_PROJECT_ID})

COUNT=0
for BUCKET_URL in $(echo $BUCKET_LIST_JSON | jq -r '.[].url'); do
    BUCKET_INFO=$(echo $BUCKET_LIST_JSON | jq -r ".[] | select(.url | contains(\"${BUCKET_URL}\"))")
    PFID=$(echo ${BUCKET_INFO} | jq -r '.metadata.labels.additionalProperties[]? | select(.key | contains("pfid")).value')
    
    if [[ ${PFID} =~ $NAMESPACE_REGEX_FILTER ]] ; then
        NS_EXIST=$(echo $KUBE_NS_LIST | grep ${BUCKET_URL} | wc -l)
        SIZE_INFO=$(gsutil du -sh ${BUCKET_URL})
        SIZE_STRING=$(echo ${SIZE_INFO} | awk '{print $1 $2}')
        SIZE_NUMBER=$(echo ${SIZE_INFO} | awk '{print $1}')
        CREATION_DATE=$(echo ${BUCKET_INFO} | jq -r '.metadata.timeCreated')
        UPDATE_DATE=$(echo ${BUCKET_INFO} | jq -r '.metadata.updated')

        CD_TS=$(date -u -d ${CREATION_DATE} '+%s')
        CURRENT_TS=$(date -u '+%s')
        DIFF_TS="$((${CURRENT_TS}-${CD_TS}))"    
        DIFF_DAY="$((${DIFF_TS}/${SECONDS_PER_DAY}))"
        
        echo "-------------------------------------"
        echo "Bucket ${BUCKET_URL}"
        echo "  Namespace     :   ${PFID}"
        echo "  NS exists     :   ${NS_EXIST}"
        echo "  Bucket size   :   ${SIZE_STRING}"
        echo "  Bucket size   :   ${SIZE_NUMBER}"
        echo "  Creation date :   ${CREATION_DATE}"
        echo "  Update date   :   ${UPDATE_DATE}"
        echo "  Age           :   ${DIFF_DAY}"
        
        if [[ ${NS_EXIST} -eq 1 ]]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
        fi

        # Skip if bucket is newer than 1d
        if [[ ${DIFF_DAY} -lt ${MIN_AGE} ]]; then
            echo "  Disk is newer than ${MIN_AGE} days : DELETION SKIPPED"
            continue;
        fi

        echo "  Command debug"
        echo "      gsutil rb ${BUCKET_URL}"

        gsutil rb ${BUCKET_URL}

        ((COUNT++))
    fi
done

echo "${COUNT} bucket(s) removed"
