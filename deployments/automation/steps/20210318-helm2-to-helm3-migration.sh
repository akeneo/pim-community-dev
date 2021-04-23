#!/bin/bash
set -eo pipefail
set -x

if [ ${STEP} == "PRE_APPLY" ]; then
    if [ -n "$(helm3 list -n ${PFID} --max 1 -o json | jq -r '.[] | select(.name == "'${PFID}'") | .name')" ]; then
        echo "[INFO] ${PFID} is already HELM3 compatible"
    else
        echo "[INFO] ${PFID} is a HELM2 released, time to convert"
        terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
        KUBECONFIG=.kubeconfig helm3 2to3 convert ${PFID} --delete-v2-releases --release-versions-max 1
    fi
fi
