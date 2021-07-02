#!/bin/bash

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* & grth-pimci* & grth-pimup* environments
for NAMESPACE in $(kubectl get ns | egrep 'srnt-pimci|srnt-pimup|grth-pimci|grth-pimup' | awk '{print $1}'); do
    NS_INFO=($(kubectl get ns | grep ${NAMESPACE}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    INSTANCE_NAME_PREFIX=pimci
    if [[ ${NAMESPACE} == srnt* ]] ; then
        TYPE="srnt"
        INSTANCE_NAME=$(echo ${NAMESPACE} | sed 's/srnt-//')
        PRODUCT_REFERENCE_TYPE=serenity_instance
        PRODUCT_REFERENCE_CODE=serenity_${ENV_NAME}
    fi
    if [[ ${NAMESPACE} == grth* ]] ; then
        TYPE="grth"
        INSTANCE_NAME=$(echo ${NAMESPACE} | sed 's/grth-//')
        PRODUCT_REFERENCE_TYPE=growth_edition_instance
        PRODUCT_REFERENCE_CODE=growth_edition_${ENV_NAME}
    fi

    DELETE_INSTANCE=false

    # delete environments that failed (not automatically removed by circleCI)
    # Theses environments are upgraded serenity / growth edition (pimup) and aged of 1 hour
    if [[ ${NAMESPACE} == srnt-pimup* ]] || [[ ${NAMESPACE} == grth-pimup* ]] ; then
        if [[ ${NS_AGE} == *h* ]] || [[ ${NS_AGE} == *d* ]] ; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimup
        fi
    fi
    # Theses environments are test deploy duplicate serenity / growth edition (pimci-duplic) and aged of 1 hour
    if [[ ${NAMESPACE} == srnt-pimci-duplic* ]] || [[ ${NAMESPACE} == grth-pimci-duplic* ]] ; then
        if [[ ${NS_AGE} == *h* ]] || [[ ${NS_AGE} == *d* ]] ; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci-duplic
        fi
    fi
    # Theses environments are test deploy serenity / growth edition (pimci) and aged of 1 hour
    if [[ ${NAMESPACE} == srnt-pimci* ]] || [[ ${NAMESPACE} == grth-pimci* ]] && ! ([[ ${NAMESPACE} == srnt-pimci-pr* ]] || [[ ${NAMESPACE} == grth-pimci-pr* ]]) ; then
        if [[ ${NS_AGE} == *h* ]] || [[ ${NS_AGE} == *d* ]] ; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci
        fi
    fi
    # Theses environments are deploy PR serenity / growth edition (pimci-pr) and aged of 1 day after the last deployment
    if [[ ${NAMESPACE} == srnt-pimci-pr* ]] || [[ ${NAMESPACE} == grth-pimci-pr* ]] ; then
        DEPLOY_TIME=$(helm3 list -n ${NAMESPACE} | grep ${NAMESPACE} | awk -F\\t '{print $4}' | awk '{print $1" "$2}')
        DAY_DIFF=$(( ($(date +%s) - $(date -d "${DEPLOY_TIME}" +%s)) / (60*60*24) ))
        if [[ ${DAY_DIFF} -ge 1 ]]; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci-pr
        fi
    fi

    if [ $DELETE_INSTANCE = true ]; then
        echo "---[TODELETE] namespace ${NAMESPACE} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
        gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/${NAMESPACE}/default.tflock || true
        # retrive the image tag with the image from a pod
        POD=$(kubectl get pods --no-headers --namespace=${NAMESPACE} -l component=pim-web | awk 'NR==1{print $1}')
        if [[ -z "$POD" ]]
        then
            kubectl delete ns ${NAMESPACE} || true
            continue
        else
            IMAGE=$(kubectl get pod --namespace=${NAMESPACE} ${POD} -o json | jq '.status.containerStatuses[].image')
            IMAGE_TAG=$(echo ${IMAGE::-1} | awk -F: '{print $2}')
        fi
        ENV_NAME=${ENV_NAME} PRODUCT_REFERENCE_TYPE=${PRODUCT_REFERENCE_TYPE} PRODUCT_REFERENCE_CODE=${PRODUCT_REFERENCE_CODE} IMAGE_TAG=${IMAGE_TAG} TYPE=${TYPE} INSTANCE_NAME=${INSTANCE_NAME} INSTANCE_NAME_PREFIX=${INSTANCE_NAME_PREFIX} PIM_SRC_DIR_GE=${PIM_SRC_DIR_GE} ACTIVATE_MONITORING=true make delete-instance
    fi
done
