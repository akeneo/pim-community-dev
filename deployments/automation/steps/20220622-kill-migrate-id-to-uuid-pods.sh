#!/bin/bash
set -eo pipefail
set -x

if [ ${STEP} == "PRE_APPLY" ]; then
    KUBECONFIG=.kubeconfig
    CRONJOB="migrate-id-to-uuid"
    PODS=$(kubectl -n ${PFID} get pods -l role=${CRONJOB} -o jsonpath='{range .items[*]}{.metadata.name}{"\n"}{end}')

    if [[ ! -z "${PODS}" ]]; then
        echo "[INFO] Existing pods for ${CRONJOB} will be killed"
        for POD in ${PODS}; do
          echo "[INFO] Killing pod ${POD}"
          kubectl -n ${PFID} delete pods ${POD} --force=true  --grace-period=0
        done
    else
        echo "[INFO] No running pods for ${CRONJOB}"
    fi
fi
