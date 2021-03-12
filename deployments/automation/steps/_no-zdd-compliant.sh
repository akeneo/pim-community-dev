#!/bin/bash
set -eo pipefail
set -x

if [ ${STEP} == "PRE_APPLY" ]; then

    terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
    export KUBECONFIG=.kubeconfig

    kubectl delete -n ${PFID} cronjob --all

    if [ ! -z ${SKIP_SHUTDOWN} ] && [ ${SKIP_SHUTDOWN} == "false" ]; then
        echo "[INFO] NO ZDD COMPLIANT RELEASE, we are turning off the application ${PFID}"
        kubectl scale -n ${PFID} deploy/pim-web deploy/pim-daemon-default --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-daemon-webhook-consumer-process --replicas=0 || true
        kubectl scale -n ${PFID} deploy/pim-daemon-all-but-linking-assets-to-products --replicas=0 || true
    else
        echo "[INFO] = ZDD COMPLIANT RELEASE Rolling update will start  ${PFID}"
    fi
fi

if [ ${STEP} == "POST_APPLY" ]; then
    if [ ! -z ${SKIP_SHUTDOWN} ] && [ ${SKIP_SHUTDOWN} == "false" ]; then

        terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
        export KUBECONFIG=.kubeconfig
        
        PARALLEL_WEB=$(yq m -x ${TF_PATH_PIM_MODULE}/pim/values.yaml tf-helm-pim-values.yaml values.yaml | yq r - pim.replicas)
        PARALLEL_DAEMON_DEFAULT=$(yq m -x ${TF_PATH_PIM_MODULE}/pim/values.yaml tf-helm-pim-values.yaml values.yaml | yq r - pim.daemons.default.replicas)
        PARALLEL_DAEMON_WEBHOOK=$(yq m -x ${TF_PATH_PIM_MODULE}/pim/values.yaml tf-helm-pim-values.yaml values.yaml | yq r - pim.daemons.webhook-consumer-process.replicas)
        PARALLEL_DAEMON_ASSET=$(yq m -x ${TF_PATH_PIM_MODULE}/pim/values.yaml tf-helm-pim-values.yaml values.yaml | yq r - pim.daemons.all-but-linking-assets-to-products.replicas)

        kubectl scale -n ${PFID} deploy/pim-web --replicas=${PARALLEL_WEB} || true
        kubectl scale -n ${PFID} deploy/pim-daemon-default --replicas=${PARALLEL_DAEMON_DEFAULT} || true
        kubectl scale -n ${PFID} deploy/pim-daemon-webhook-consumer-process --replicas=${PARALLEL_DAEMON_WEBHOOK} || true
        kubectl scale -n ${PFID} deploy/pim-daemon-all-but-linking-assets-to-products --replicas=${PARALLEL_DAEMON_ASSET} || true
    fi
fi
