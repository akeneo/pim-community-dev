#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then

    if [ -z $(yq r -j -P "${PWD}/main.tf.json" 'module.pim.product_reference_code') ]; then
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim.product_reference_code' "FillMePlease"
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim-monitoring.product_reference_code' "FillMePlease"
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim.product_reference_type' "FillMePlease"
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim-monitoring.product_reference_type' "FillMePlease"
    else
        echo "[INFO] product type & code already managed by terraform"
    fi
fi
