#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    # if the mysql PVC class is not ssd-retain-csi, migration must be done
    KUBECONFIG=.kubeconfig
    if [ $(kubectl -n ${PFID} get pvc data-mysql-server-0 -o jsonpath='{.spec.storageClassName}') = "ssd-retain" ]; then
        echo "[INFO] mysql disk not in class ssd-retain-csi. Beginning migration..."
        set -x
        kubectl -n ${PFID} delete deploy/mysql
        # Remove PVC & PV
        PV=$(kubectl get pv -n ${PFID} -o json | jq -r ".items[] | select ( (.spec.claimRef.name == \"data-mysql-server-0\") and .spec.claimRef.namespace==\"$PFID\") | .metadata.name")
        echo "PV=$PV"
        kubectl -n ${PFID} delete pvc data-mysql-server-0
        kubectl -n ${PFID} delete pv ${PV}
    else
        echo "[INFO] mysql disk already in class ssd-retain-csi"
    fi
fi
