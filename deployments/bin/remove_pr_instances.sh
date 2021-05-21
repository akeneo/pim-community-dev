#!/bin/bash

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* & grth-pimci* & grth-pimup* environments
for NAMESPACE in $(kubectl get ns | egrep 'srnt-pimci|srnt-pimup|grth-pimci|grth-pimup' | awk '{print $1}'); do
    NS_INFO=($(kubectl get ns | grep ${NAMESPACE}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    if [[ ${NAMESPACE} == *srnt* ]] ; then
        INSTANCE_NAME=$(echo ${NS_INFO[0]//srnt-/})
        TYPE="srnt"
        PRODUCT_REFERENCE_TYPE=serenity_instance
        PRODUCT_REFERENCE_CODE=serenity_$(ENV_NAME)
    fi
    if [[ ${NAMESPACE} == *grth* ]] ; then
        INSTANCE_NAME=$(echo ${NS_INFO[0]//grth-/})
        TYPE="grth"
        PRODUCT_REFERENCE_TYPE=growth_edition_instance
        PRODUCT_REFERENCE_CODE=growth_edition_$(ENV_NAME)
    fi

    DELETE_INSTANCE=false

    # delete environments that failed (not automatically removed by circleCI)
    # Theses environments are upgraded serenity / growth edition (pimup) and aged of 1hour
    if [[ ${NAMESPACE} == *srnt-pimup* ]] || [[ ${NAMESPACE} == *grth-pimup* ]] ; then
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

        # if TYPE="grth", we need to find the IMAGE_TAG
        # retrive the image tag with the image from a pod
        if [[ ${TYPE} == grth ]] ; then
            POD=$(kubectl get pods --namespace=${NAMESPACE} -l component=pim-daemon-default | awk '/pim-daemon-default/ {print $1}')
            if [[ -z "$POD" ]]
            then
                kubectl delete ns ${NAMESPACE} || true
                continue
            else
                IMAGE=$(kubectl get pod --namespace=${NAMESPACE} ${POD} -o json | jq '.status.containerStatuses[].image')
                IMAGE_TAG=$(echo ${IMAGE::-1} | awk -F: '{print $2}')
            fi
        else
            IMAGE_TAG="master"
        fi
        PRODUCT_REFERENCE_TYPE=${PRODUCT_REFERENCE_TYPE} PRODUCT_REFERENCE_CODE=${PRODUCT_REFERENCE_CODE} IMAGE_TAG=${IMAGE_TAG} TYPE=${TYPE} INSTANCE_NAME=${INSTANCE_NAME} ACTIVATE_MONITORING=true make delete-instance
    fi
done
