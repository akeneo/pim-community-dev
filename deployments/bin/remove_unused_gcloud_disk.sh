#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
LIMIT=100000
SECONDS_PER_HOUR=$((60*60))
DEFAULT_KEEP_ALIVE_PERIOD=2 # How many hours we should keep an unused disk
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"
NAME_REGEX_FILTER="^((pvc)|(gke-europe)|(grth|srnt|tria)-pim(ci|up))"
# NAMESPACE_REGEX_FILTER="^.*"

# List of gcloud physical disk with a description and no usage (users) in ${GOOGLE_PROJECT_ID}
PD_LIST=$(gcloud compute disks list --limit ${LIMIT} --format json | jq -c '.[] | select(.users == null)')

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

REASON_NS_EXIST=0
REASON_TOO_YOUNG=0
REASON_NAME_NOT_MATCH=0

DELETION_COUNT=0
for PD in $PD_LIST; do
    NAME=$(echo $PD | jq -r '.name')
    ZONE=$(echo $PD | jq -r '.zone' | cut -d/ -f9)
    CREATION_DATE=$(echo $PD | jq -r '.creationTimestamp')
    ATTACH_DATE=$(echo $PD | jq -r '.lastAttachTimestamp')
    DETACH_DATE=$(echo $PD | jq -r '.lastDetachTimestamp')
    KIND=$(echo $PD | jq -r '.kind')
    SIZE=$(echo $PD | jq -r '.sizeGb')
    STATUS=$(echo $PD | jq -r '.status')
    DESCRIPTION=$(echo $PD | jq -r '.description')
    USAGE_COUNT=$(echo $PD | jq -r '.users | length')
    KEEP_ALIVE_PERIOD=${DEFAULT_KEEP_ALIVE_PERIOD}

    echo "-------------------------------------"
    echo "PD ${NAME}"
    echo "  Type            :     ${KIND}"
    echo "  Size            :     ${SIZE} Gb"
    echo "  Zone            :     ${ZONE}"
    echo "  Status          :     ${STATUS}"
    echo "  In use          :     ${USAGE_COUNT}"
    echo "  Creation date   :     ${CREATION_DATE}"

    REFERENCE_DATE=${CREATION_DATE}
    
    if [ ! -z "${ATTACH_DATE}" ] && [ "${ATTACH_DATE}" != "null" ]; then
        REFERENCE_DATE=${ATTACH_DATE}
    fi

    if [ ! -z "${DETACH_DATE}" ] && [ "${DETACH_DATE}" != "null" ]; then
        REFERENCE_DATE=${DETACH_DATE}
    fi
    echo "  Last usage date :     ${REFERENCE_DATE}"

    if [[ ! ${NAME} =~ ${NAME_REGEX_FILTER} ]]; then
        echo "  Name doesn't match \"${NAME_REGEX_FILTER}\" : DELETION SKIPPED"
        ((REASON_NAME_NOT_MATCH++))
        continue
    fi

    # if description is not null then we get pv, pvc and namespace from it
    if [[ ! "${DESCRIPTION}" = "null" ]]; then
        NAMESPACE=$(echo $DESCRIPTION | jq -r '.[keys[] | select(endswith("pvc/namespace"))]' || echo "")
        PV=$(echo $DESCRIPTION | jq -r '.[keys[] | select(endswith("pv/name"))]' || echo "")
        PVC=$(echo $DESCRIPTION | jq -r '.[keys[] | select(endswith("pvc/name"))]' || echo "")

        echo "  Namespace       :     ${NAMESPACE}"
        echo "  PV              :     ${PV}"
        echo "  PVC             :     ${PVC}"

        if [ ! -z ${NAMESPACE} ] && [ "${NAMESPACE}" != "null" ] ; then
            KEEP_ALIVE_PERIOD=1
            NS_EXIST=$(echo $KUBE_NS_LIST | grep -w ${NAMESPACE} | wc -l)
            echo "  NS exists       :     ${NS_EXIST}"
    
            # Skip if disk is linked to an existing namespace
            if [[ ${NS_EXIST} -eq 1 ]]; then
                echo "  Existing namespace : DELETION SKIPPED"
                ((REASON_NS_EXIST++))
                continue
            fi
        fi
    fi
    
    PD_TS=$(date -u -d ${REFERENCE_DATE} '+%s')
    CURRENT_TS=$(date -u '+%s')
    DIFF_TS="$((${CURRENT_TS}-${PD_TS}))"    
    DIFF_HOUR="$((${DIFF_TS}/${SECONDS_PER_HOUR}))"
    echo "  Age             :     ${DIFF_HOUR}h"

    # Skip if disk is newer than ${KEEP_ALIVE_PERIOD} days
    if [[ ${DIFF_HOUR} -lt ${KEEP_ALIVE_PERIOD} ]]; then
        echo "  Disk last usage newer than ${KEEP_ALIVE_PERIOD} day(s) : DELETION SKIPPED"
        ((REASON_TOO_YOUNG++))
        continue;
    fi

    echo "  Command debug"
    echo "      gcloud --quiet compute disks delete ${NAME} --project=${GOOGLE_PROJECT_ID} --zone=${ZONE} || true"

    gcloud --quiet compute disks delete ${NAME} --project=${GOOGLE_PROJECT_ID} --zone=${ZONE} || true
    
    ((DELETION_COUNT++))
done

TOTAL_DISK=$(echo $PD_LIST | wc -l)
echo "Over a total of ${TOTAL_DISK} disk(s) :"
echo "  * ${DELETION_COUNT} disk(s) have been removed"
echo "  * ${REASON_NAME_NOT_MATCH} disk(s) does not match name format"
echo "  * ${REASON_NS_EXIST} disk(s) have link to an existing namespace"
echo "  * ${REASON_TOO_YOUNG} disk(s) are too young for deletion"
