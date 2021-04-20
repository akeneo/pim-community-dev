#!/bin/bash
set -eo pipefail
set -x

if [ ${STEP} == "PRE_INIT" ]; then
    if (helm2 list ${PFID}|grep -q ${PFID}); then
        echo "[INFO] ${PFID} is a HELM2 released, time to convert"
        helm3 2to3 convert ${PFID} --delete-v2-releases
    else
        echo "[INFO] ${PFID} not exists or is already HELM3 compatible"
    fi
fi
