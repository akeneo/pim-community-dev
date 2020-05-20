#!/bin/bash -eo pipefail

for namespace in $(kubectl get ns|egrep 'srnt-pimci|srnt-pimup'|awk '{print $1}'); do
	NS_INFO=($(kubectl get ns |grep ${namespace}))
	NS_STATUS=$(echo ${NS_INFO[1]})
	NS_AGE=$(echo ${NS_INFO[2]})
	INSTANCE_NAME=$(echo ${NS_INFO[0]//srnt-})
    if [[ ${NS_AGE} == *h* ]]; then
        if [[ ${#NS_AGE} -eq 3 ]]; then
            #ex : 23h
            AGE=$(echo ${NS_AGE//h})
            if [[ ${AGE} -gt 23 ]]; then
                echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
                INSTANCE_NAME=${INSTANCE_NAME} make create-ci-release-files delete
            fi
        fi
    fi
    if [[ ${NS_AGE} == *d* ]]; then
        #ex: 2d
        echo "---[TODELETE] namespace ${namespace} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
        INSTANCE_NAME=${INSTANCE_NAME} make create-ci-release-files delete
    fi
done
