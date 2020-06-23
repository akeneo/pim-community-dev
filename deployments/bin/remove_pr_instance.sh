#!/bin/bash 

for namespace in $(kubectl get ns | egrep 'srnt-pimci|srnt-pimup' | awk '{print $1}'); do
    NS_INFO=($(kubectl get ns | grep ${namespace}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    INSTANCE_NAME=$(echo ${NS_INFO[0]//srnt-/})
    # delete environments that failed (not automatically removed by circleCI)
    if [[ ${NAMESPACE} == *srnt-pimup* ]]; then
        if [[ ${NS_AGE} == *h* ]]; then
            echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
            if [ $(gsutil ls gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock 2>/dev/null) ]; then
                gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock
            fi
            INSTANCE_NAME=${INSTANCE_NAME} make create-ci-release-files delete
        fi
    fi
    # delete environments older than 24 hours
    if [[ ${NS_AGE} == *h* ]]; then
        if [[ ${#NS_AGE} -eq 3 ]]; then
            #ex : 23h
            AGE=$(echo ${NS_AGE//h/})
            if [[ ${AGE} -gt 23 ]]; then
                echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
                if [ $(gsutil ls gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock 2>/dev/null) ]; then
                        gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock
                fi
                INSTANCE_NAME=${INSTANCE_NAME} make create-ci-release-files delete
            fi
        fi
    fi
    if [[ ${NS_AGE} == *d* ]]; then
        #ex: 2d
        echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
        if [ $(gsutil ls gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock 2>/dev/null) ]; then
                gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock
        fi
        INSTANCE_NAME=${INSTANCE_NAME} make create-ci-release-files delete
    fi
done
