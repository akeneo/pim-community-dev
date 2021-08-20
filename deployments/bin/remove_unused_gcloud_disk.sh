#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
LIMIT=100000
SECONDS_PER_DAY=$((24*60*60))
CSV_OUTPUT="$(pwd)/logs.csv"
CMD_OUTPUT="$(pwd)/cmd.sh"
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"
# NAMESPACE_REGEX_FILTER="^.*"

# CSV Headers
echo "NAME,CREATION_DATE,AGE (days),KIND,SIZE,STATUS,PV,NAMESPACE,NS EXIST IN KUBE,PVC,USAGE_COUNT" > $CSV_OUTPUT

# BASH file header
echo "#!/usr/bin/bash" > $CMD_OUTPUT

# List of gcloud physical disk with a description and no usage (users) in ${GOOGLE_PROJECT_ID}
PD_LIST=$(gcloud compute disks list --limit ${LIMIT} --format json | jq -c '.[] | select(.description != null) | select(.users == null)')

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

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
        PD_TS=$(date -u -d ${CREATION_DATE} '+%s')
        CURRENT_TS=$(date -u '+%s')
        DIFF_TS="$((${CURRENT_TS}-${PD_TS}))"    
        DIFF_DAY="$((${DIFF_TS}/${SECONDS_PER_DAY}))"
        NS_EXIST=$(echo $KUBE_NS_LIST | grep ${NAMESPACE} | wc -l)

        # Skip if disk is newer than 1d
        if [[ ${DIFF_DAY} -lt 1 ]]; then
            continue;
        fi

        # Skip if disk is linked to an existing namespace
        if [[ ${NS_EXIST} -eq 1 ]]; then
            continue;
        fi

        # Send disk info into csv file
        echo "${NAME},${CREATION_DATE},${DIFF_DAY},${KIND},${SIZE},${STATUS},${PV},${NAMESPACE},${NS_EXIST},${PVC},${USAGE_COUNT}" >> ${CSV_OUTPUT}
        echo "gcloud --quiet compute disks delete ${NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE} || true" >> ${CMD_OUTPUT}
        echo "sleep 2" >> ${CMD_OUTPUT}
    fi
done
