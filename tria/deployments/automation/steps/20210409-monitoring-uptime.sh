#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_INIT" ]; then
    if [ -z $(yq r -j -P "${PWD}/main.tf.json" 'module.pim-monitoring') ]; then
        echo "[INFO] No monitoring resource to configure"
    else
        if [ -z $(yq r -j -P "${PWD}/main.tf.json" 'module.pim-monitoring.monitoring_authentication_token') ]; then
            yq w -j -P -i ${PWD}/main.tf.json 'module.pim-monitoring.monitoring_authentication_token' '${module.pim.monitoring_authentication_token}'
        else
            echo "[INFO] Attribute monitoring_authentication_token for pim-monitoring already added"
        fi
    fi
fi
