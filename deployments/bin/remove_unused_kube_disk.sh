#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

RELEASED_PV_LIST=$(kubectl get pv -o json | jq -r '[.items[] | select(.status.phase == "Released") | select(.spec.claimRef.namespace|test("(srnt|grth|tria)-pim(ci|up)-pr-.*")) | .metadata.name] | unique | .[]')
RELEASED_PV_COUNT=$(echo "${RELEASED_PV_LIST}" | wc -l)
echo "Found ${RELEASED_PV_COUNT} released PVs"

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* & grth-pimci* & grth-pimup* & tria-pimci* environments
for RELEASED_PV in $RELEASED_PV_LIST; do
    echo "--------------------------------------"
    echo "PV : ${RELEASED_PV}"
    
    PD_DATA=$(kubectl get pv --field-selector metadata.name=${RELEASED_PV} -o=jsonpath='["{..spec.claimRef.namespace}","{..spec.csi.volumeHandle}","{..metadata.creationTimestamp}"]')
    NAMESPACE=$(echo $PD_DATA | jq -r '.[0]')
    echo "  Namespace :     ${NAMESPACE}"


    if [[ ${NAMESPACE} =~ $NAMESPACE_REGEX_FILTER ]] ; then

        NAMESPACE_EXIST=$(echo $KUBE_NS_LIST | grep -w ${NAMESPACE} | wc -l)
        if [ "${NAMESPACE_EXIST}" -eq 1 ]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
        fi

        PD_NAME=$(echo $PD_DATA | jq -r '.[1]' | basename)
        echo "  PD :            ${PD_NAME}"
        
        PD_CREATION_TIME=$(echo $PD_DATA | jq -r '.[2]')
        echo "  Created at :    ${PD_CREATION_TIME}"

        PD_TS=$(date -u -d ${PD_CREATION_TIME} '+%s')
        YESTERDAY_TS=$(date -u -d "yesterday" '+%s')
        if [ $YESTERDAY_TS -le $PD_TS ]; then
            echo "  PV newer than 1 day : DELETION SKIPPED"
            continue
        fi

        echo "  Command debug"
        echo "      kubectl delete pv ${RELEASED_PV} -n ${NAMESPACE}"
        echo "      gcloud --quiet compute disks delete ${PD_NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE}"
        kubectl delete pv ${RELEASED_PV} -n ${NAMESPACE}
        gcloud --quiet compute disks delete ${PD_NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE}
    else
        echo "  Namespace doesn't match : DELETION SKIPPED"
    fi
    # exit;
done
