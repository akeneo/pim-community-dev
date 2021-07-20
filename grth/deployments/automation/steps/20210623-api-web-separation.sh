#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    if [ -z $(yq r "${PWD}/values.yaml" 'pim.replicas') ]; then
        echo "[INFO] No replicas to configure"
    else
        if [ -z $(yq r "${PWD}/values.yaml" 'pim.web.replicas') ]; then
            yq w -i ${PWD}/values.yaml 'pim.web.replicas' "$(yq r ${PWD}/values.yaml 'pim.replicas')"
            yq d -i ${PWD}/values.yaml 'pim.replicas'
        else
            echo "[INFO] Replicas already configured"
        fi
        
    fi

    if [ "z" == "z$(yq r "${PWD}/values.yaml" 'pim.fpm.resources')" ]; then
        echo "[INFO] No fpm resources to configure"
    else
        if [ "z" == "z$(yq r "${PWD}/values.yaml" 'pim.web.fpm.resources')" ]; then
            yq r ${PWD}/values.yaml 'pim.fpm' | yq p - 'pim.web.fpm' | yq m -i ${PWD}/values.yaml -
            yq d -i ${PWD}/values.yaml 'pim.fpm'
        else
            echo "[INFO] fpm resources already configured"
        fi
    fi

    if [ "z" == "z$(yq r "${PWD}/values.yaml" 'pim.httpd.resources')" ]; then
        echo "[INFO] No httpd resources to configure"
    else
        if [ "z" == "z$(yq r "${PWD}/values.yaml" 'pim.web.httpd.resources')" ]; then
            yq r ${PWD}/values.yaml 'pim.httpd' | yq p - 'pim.web.httpd' | yq m -i ${PWD}/values.yaml -
            yq d -i ${PWD}/values.yaml 'pim.httpd'
        else
            echo "[INFO] httpd resources already configured"
        fi
    fi
  
fi
