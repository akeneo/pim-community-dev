#!/bin/bash
set -eo pipefail
set -x

if [ ${STEP} == "PRE_APPLY" ]; then
    kubectl delete -n ${PFID} cronjob --all

    if [ ! -z ${SKIP_SHUTDOWN} ] && [ ${SKIP_SHUTDOWN} == "false" ]; then
        echo "[INFO] NO ZDD COMPLIANT RELEASE, we are turning off the application ${PFID}"
        kubectl scale -n ${PFID} deploy/pim-web --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-api --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-daemon-webhook-consumer-process --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-daemon-job-consumer-process --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-daemon-default --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-daemon-all-but-linking-assets-to-products --replicas=0 || true
    else
        echo "[INFO] = ZDD COMPLIANT RELEASE Rolling update will start  ${PFID}"
    fi
fi
