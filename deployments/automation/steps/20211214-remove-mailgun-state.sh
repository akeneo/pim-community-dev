#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    if [[ $(terraform state list | grep module.pim.null_resource.mailgun_credential) == "module.pim.null_resource.mailgun_credential" ]]; then
        echo "[INFO] Removing the old mailgun state"
        terraform state rm module.pim.null_resource.mailgun_credential
    else
        echo "[INFO] No mailgun_credential null_resource have been detected. Nothing to do!"
    fi
fi
