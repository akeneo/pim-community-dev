#!/bin/bash

function delete_terraform_lockfile {
    INSTANCE_NAME=$1
    if [[ $(gsutil ls gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock 2>/dev/null) ]]; then
        sleep 1
        gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock; returnCode=$?
        if [[ $returnCode -eq 0 ]]; then
            echo "Terrafom lock file found and deleted : gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock"
        else
            echo "ERR : Terrafom lock file found but not deleted : gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock"
        fi
    else
        echo "Terrafom lock file not found : gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/default.tflock"
    fi
}

# Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* environments
for namespace in $(kubectl get ns | egrep 'srnt-pimci|srnt-pimup' | awk '{print $1}'); do
    NS_INFO=($(kubectl get ns | grep ${namespace}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    INSTANCE_NAME=$(echo ${NS_INFO[0]//srnt-/})
    # delete environments that failed (not automatically removed by circleCI)
    # Theses environments are upgraded serenity (pimup) and aged of 1hour
    if [[ ${NAMESPACE} == *srnt-pimup* ]]; then
        if [[ ${NS_AGE} == *h* ]]; then
            echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
            delete_terraform_lockfile ${INSTANCE_NAME}
            INSTANCE_NAME=${INSTANCE_NAME} make delete-serenity
        fi
    fi
    # delete environments older than 24 hours
    if [[ ${NS_AGE} == *h* ]]; then
        if [[ ${#NS_AGE} -eq 3 ]]; then
            AGE=$(echo ${NS_AGE//h/})
            if [[ ${AGE} -gt 23 ]]; then
                echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
                delete_terraform_lockfile ${INSTANCE_NAME}
                INSTANCE_NAME=${INSTANCE_NAME} make delete-serenity
            fi
        fi
    fi
    # delete all environments older than 24 hours (>=1day)
    if [[ ${NS_AGE} == *d* ]]; then
        echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
        delete_terraform_lockfile ${INSTANCE_NAME}
        INSTANCE_NAME=${INSTANCE_NAME} make delete-serenity
    fi
done
