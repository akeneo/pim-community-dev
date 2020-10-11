#!/bin/bash

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* environments
for NAMESPACE in $(kubectl get ns | egrep 'srnt-pimci|srnt-pimup' | awk '{print $1}'); do
    NS_INFO=($(kubectl get ns | grep ${NAMESPACE}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    INSTANCE_NAME=$(echo ${NS_INFO[0]//srnt-/})

    DELETE_INSTANCE=false

    # delete environments that failed (not automatically removed by circleCI)
    # Theses environments are upgraded serenity (pimup) and aged of 1hour
    if [[ ${NAMESPACE} == *srnt-pimup* ]]; then
        if [[ ${NS_AGE} == *h* ]]; then
            DELETE_INSTANCE=true
        fi
    fi
    # delete environments older than 24 hours
    if [[ ${NS_AGE} == *h* ]]; then
        if [[ ${#NS_AGE} -eq 3 ]]; then
            AGE=$(echo ${NS_AGE//h/})
            if [[ ${AGE} -gt 23 ]]; then
                DELETE_INSTANCE=true
            fi
        fi
    fi
    # delete all environments older than 24 hours (>=1day)
    if [[ ${NS_AGE} == *d* ]]; then
        DELETE_INSTANCE=true
    fi

    if [ $DELETE_INSTANCE = true ]; then
        echo "---[TODELETE] namespace ${NAMESPACE} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
        gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock || true
        INSTANCE_NAME=${INSTANCE_NAME} make delete-serenity
    fi
done
