#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"

RELEASED_PV_LIST=$(kubectl get pv -o json | jq -r '[.items[] | select(.status.phase == "Released") | select(.spec.claimRef.namespace|test("(srnt|grth|tria)-pim(ci|up)-pr-.*")) | .metadata.name] | unique | .[]')
RELEASED_PV_COUNT=$(echo "${RELEASED_PV_LIST}" | wc -l)
echo "Found ${RELEASED_PV_COUNT} released PVs"

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* & grth-pimci* & grth-pimup* & tria-pimci* environments
for RELEASED_PV in $RELEASED_PV_LIST; do
    echo "--------------------------------------"
    PD_DATA=$(kubectl get pv --field-selector metadata.name=${RELEASED_PV} -o=jsonpath='["{..spec.claimRef.namespace}","{..spec.gcePersistentDisk.pdName}","{..metadata.creationTimestamp}"]')
    NAMESPACE=$(echo $PD_DATA | jq -r '.[0]')
    PD_NAME=$(echo $PD_DATA | jq -r '.[1]')
    PD_CREATION_TIME=$(echo $PD_DATA | jq -r '.[2]')
    echo "PV : ${RELEASED_PV}"
    echo "  Created at :    ${PD_CREATION_TIME}"
    echo "  Namespace :     ${NAMESPACE}"
    echo "  PD :            ${PD_NAME}"

    PD_TS=$(date -u -d ${PD_CREATION_TIME} '+%s')
    YESTERDAY_TS=$(date -u -d "yesterday" '+%s')
    if [ $YESTERDAY_TS -le $PD_TS ]; then
        echo "  PV newer than 1 day"
        continue
    else
        echo "  PV older than 1 day"
    fi


    NAMESPACE_EXIST=$(kubectl get ns | grep ${NAMESPACE} | wc -l)
    if [ "${NAMESPACE_EXIST}" -eq "0" ]; then
        echo "  Namespace doesn't exist. PV and PD will be removed";
        echo "  Command debug"
        echo "      kubectl delete pv ${RELEASED_PV} -n ${NAMESPACE}"
        echo "      gcloud --quiet compute disks delete ${PD_NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE}"
        kubectl delete pv ${RELEASED_PV} -n ${NAMESPACE}
        gcloud --quiet compute disks delete ${PD_NAME} --project=${GOOGLE_PROJECT_ID} --zone=${GOOGLE_CLUSTER_ZONE}
        sleep 2
    fi
    # exit;
done
