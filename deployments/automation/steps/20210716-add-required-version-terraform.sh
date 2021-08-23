#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_INIT" ]; then
    if [[ $(yq r -j -P "${PWD}/main.tf.json" 'terraform.required_version') == "" ]]; then
        echo "[INFO] Time to add the terraform version 0.12.25 in main.tf.json"
        yq w -j -P -i ${PWD}/main.tf.json 'terraform.required_version' "= 0.12.25"
    else
        echo "[INFO] Terraform version is already set in main.tf.json. Nothing to do!"
    fi
fi
