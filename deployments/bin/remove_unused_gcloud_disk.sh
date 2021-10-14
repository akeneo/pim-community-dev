#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
LIMIT=100000
SECONDS_PER_DAY=$((24*60*60))
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"
# NAMESPACE_REGEX_FILTER="^.*"

# List of gcloud physical disk with a description and no usage (users) in ${GOOGLE_PROJECT_ID}
PD_LIST=$(gcloud compute disks list --limit ${LIMIT} --format json | jq -c '.[] | select(.description != null) | select(.users == null)')

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

COUNT=0
for PD in $PD_LIST; do
    NAME=$(echo $PD | jq -r '.name')
    CREATION_DATE=$(echo $PD | jq -r '.creationTimestamp')
    KIND=$(echo $PD | jq -r '.kind')
    SIZE=$(echo $PD | jq -r '.sizeGb')
    STATUS=$(echo $PD | jq -r '.status')
    DESCRIPTION=$(echo $PD | jq -r '.description')
    PV=""
    NAMESPACE=""
    PVC=""
    USAGE_COUNT=$(echo $PD | jq -r '.users | length')

    # if description is not null then we get pv, pvc and namespace from it
    if [[ ! "${DESCRIPTION}" = "null" ]]; then
        PV=$(echo $DESCRIPTION | jq -r '.[keys[] | select(endswith("pv/name"))]' || echo "")
        NAMESPACE=$(echo $DESCRIPTION | jq -r '.[keys[] | select(endswith("pvc/namespace"))]' || echo "")
        PVC=$(echo $DESCRIPTION | jq -r '.[keys[] | select(endswith("pvc/name"))]' || echo "")
    fi


    if [[ ${NAMESPACE} =~ $NAMESPACE_REGEX_FILTER ]] ; then
        echo "-------------------------------------"
        echo "PD ${NAME}"
        echo "  Namespace     :     ${NAMESPACE}"

        NS_EXIST=$(echo $KUBE_NS_LIST | grep -w ${NAMESPACE} | wc -l)
        echo "  NS exists     :     ${NS_EXIST}"

        # Skip if disk is linked to an existing namespace
        if [[ ${NS_EXIST} -eq 1 ]]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
        fi

        echo "  Type          :     ${KIND}"
        echo "  Size          :     ${SIZE}"
        echo "  Status        :     ${STATUS}"
        echo "  PV            :     ${PV}"
        echo "  PVC           :     ${PVC}"
        echo "  In use        :     ${USAGE_COUNT}"
        echo "  Creation date :     ${CREATION_DATE}"
        
        PD_TS=$(date -u -d ${CREATION_DATE} '+%s')
        CURRENT_TS=$(date -u '+%s')
        DIFF_TS="$((${CURRENT_TS}-${PD_TS}))"    
        DIFF_DAY="$((${DIFF_TS}/${SECONDS_PER_DAY}))"
        echo "  Age           :     ${DIFF_DAY}"

        # Skip if disk is newer than 1d
        if [[ ${DIFF_DAY} -lt 1 ]]; then
            continue;
        fi

        echo "  Command debug"
        echo "      gcloud --quiet compute disks delete ${NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE} || true"
        gcloud --quiet compute disks delete ${NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE} || true
        
        ((COUNT++))
    fi
done

echo "${COUNT} disk(s) removed"
