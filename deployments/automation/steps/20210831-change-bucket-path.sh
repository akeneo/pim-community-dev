#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_INIT" ]; then
    if [[ $(yq r "${PWD}/main.tf.json" 'module.pim.product_reference_type') == "growth_edition_instance" ]]; then
        if [[ $(yq r "${PWD}/main.tf.json" 'module.pim.source') =~ \/\/deployments\/terraform$ ]]; then
            echo "[INFO] Path for GRTH is already correct. Nothing to do!"
        else
            echo "[INFO] Changing the path for grth in main.tf.json"
            # growth-v[0-9]{14} represents the image tag for the prod
            # growth-[0-9a-fA-F]{33} represents the image tag for the CI
            sed -i -E 's#(growth-(v[0-9]{14}|[0-9a-fA-F]{33}))(/terraform)#\1//deployments\3#g' ${PWD}/main.tf.json
        fi
    else
        echo "[INFO] Not a GRTH release. Nothing to do!"
    fi
fi
