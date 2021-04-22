#!/bin/bash
set -eo pipefail
set -x

if [ ${STEP} == "PRE_INIT" ]; then
    if [ -n "$(helm3 list -n ${PFID} --max 1 -o json | jq -r '.[] | select(.name == "'${PFID}'") | .name')" ]; then
        echo "[INFO] ${PFID} is already HELM3 compatible"
    else
        echo "[INFO] ${PFID} is a HELM2 released, time to convert"
        helm3 2to3 convert ${PFID} --delete-v2-releases
    fi
fi
