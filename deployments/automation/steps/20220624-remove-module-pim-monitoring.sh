#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    if [[ $(yq r -j -P "${PWD}/main.tf.json" 'module.pim-monitoring') != "" ]]; then
        echo "[INFO] Destroy module pim-monitoring"
        terraform destroy -auto-approve -input=false --target module.pim-monitoring

        echo "[INFO] Remove pim-monitoring module in main.tf.json"
        yq d -i -P -j ${PWD}/main.tf.json 'module.pim-monitoring'
    else
        echo "[INFO] No pim_monitoring module to remove"
    fi
fi
