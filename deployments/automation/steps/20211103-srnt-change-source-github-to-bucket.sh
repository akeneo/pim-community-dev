#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_INIT" ]; then
    if [[ $(yq r "${PWD}/main.tf.json" 'module.pim.product_reference_type') == "serenity_instance" ]]; then
        if [[ $(yq r "${PWD}/main.tf.json" 'module.pim.source') =~ gcs::https:\/\/www.googleapis.com ]]; then
            echo "[INFO] Path for SRNT is already correct. Nothing to do!"
        else
            echo "[INFO] Changing the path for srnt in main.tf.json"
            PIM_VERSION=$(yq r "${PWD}/main.tf.json" 'module.pim.pim_version')
            if [[ $(yq r "${PWD}/main.tf.json" 'module.pim.product_reference_code') =~ serenity_dev ]]; then
                # Dev mode
                yq w -j -P -i ${PWD}/main.tf.json 'module.pim.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/serenity-edition-dev/${PIM_VERSION}//deployments/terraform"
                if [[ -z $(yq r "${PWD}/main.tf.json" 'module.pim-monitoring.source') ]]; then
                  echo "[INFO] No monitoring activated!"
                else
                  yq w -j -P -i ${PWD}/main.tf.json 'module.pim-monitoring.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/serenity-edition-dev/${PIM_VERSION}//deployments/terraform/monitoring"
                fi
            else
                # Prod mode
                yq w -j -P -i ${PWD}/main.tf.json 'module.pim.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/serenity-edition/${PIM_VERSION}//deployments/terraform"
                if [[ -z $(yq r "${PWD}/main.tf.json" 'module.pim-monitoring.source') ]]; then
                  echo "[INFO] No monitoring activated!"
                else
                  yq w -j -P -i ${PWD}/main.tf.json 'module.pim-monitoring.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/serenity-edition/${PIM_VERSION}//deployments/terraform/monitoring"
                fi
            fi
        fi
    else
        echo "[INFO] Not a SRNT release. Nothing to do!"
    fi
fi
