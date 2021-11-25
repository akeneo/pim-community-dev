#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_INIT" ]; then
    if [[ $(yq r "${PWD}/main.tf.json" 'terraform.required_version') == "= 0.12.25" ]]; then
        echo "[INFO] Time to upgrade terraform version to 0.13.7 in main.tf.json"
        yq w -j -P -i ${PWD}/main.tf.json 'terraform.required_version' "= 0.13.7"
    else
        echo "[INFO] Terraform version isn't 0.12.25 in main.tf.json. Nothing to do!"
    fi
fi
