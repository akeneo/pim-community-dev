#!/bin/bash

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* & grth-pimci* environments
for NAMESPACE in $(kubectl get ns | egrep 'srnt-pimci|srnt-pimup|grth-pimci' | awk '{print $1}'); do
    NS_INFO=($(kubectl get ns | grep ${NAMESPACE}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    if [[ ${NAMESPACE} == *srnt* ]] ; then
        INSTANCE_NAME=$(echo ${NS_INFO[0]//srnt-/})
        TYPE="srnt"
    fi
    if [[ ${NAMESPACE} == *grth* ]] ; then
        INSTANCE_NAME=$(echo ${NS_INFO[0]//grth-/})
        TYPE="grth"
    fi

    DELETE_INSTANCE=false

    # delete environments that failed (not automatically removed by circleCI)
    # Theses environments are upgraded serenity (pimup) or growth edition (grth-pimci) and aged of 1hour
    if [[ ${NAMESPACE} == *srnt-pimup* ]] || [[ ${NAMESPACE} == *grth-pimci* ]] ; then
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
        gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/${NAMESPACE}/default.tflock || true
        TYPE=${TYPE} INSTANCE_NAME=${INSTANCE_NAME} ACTIVATE_MONITORING=true make delete-instance
    fi
done
