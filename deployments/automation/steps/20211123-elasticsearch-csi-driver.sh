#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    KUBECONFIG=.kubeconfig
    SSD_RETAIN_PVCS=$(kubectl -n ${PFID} get pvc -l 'app in (elasticsearch),component notin (client)' -o json | jq -r '.items[] | select(.spec.storageClassName == "ssd-retain")|.metadata.name')
    # if the some ES PVC class is still in ssd-retain, migration must be done
    if [ -n "${SSD_RETAIN_PVCS}" ]; then
        echo "[INFO] elasticsearch disks not in class ssd-retain-csi. Beginning migration..."
        set -x
        kubectl -n ${PFID} delete sts elasticsearch-master
        kubectl -n ${PFID} delete sts elasticsearch-data
        for PVC in ${SSD_RETAIN_PVCS}; do
            # Remove PVC & recreate PV with same disk
            echo "PVC=${PVC}"
            PV=$(kubectl -n ${PFID} get pv -o json | jq -r ".items[] | select ( (.spec.claimRef.name == \"${PVC}\") and .spec.claimRef.namespace==\"$PFID\" and .status.phase==\"Bound\") | .metadata.name")
            echo "PV=${PV}"
            kubectl -n ${PFID} get pv ${PV} -o yaml > ${PVC}_pv.yaml
            DISK=$(yq r ${PVC}_pv.yaml 'spec.gcePersistentDisk.pdName')
            echo "DISK=${DISK}"
            yq w -i ${PVC}_pv.yaml 'spec.storageClassName' 'ssd-retain-csi'
            yq w -i ${PVC}_pv.yaml 'spec.csi.driver' 'pd.csi.storage.gke.io'
            yq w -i ${PVC}_pv.yaml 'spec.csi.fsType' 'ext4'
            yq w -i ${PVC}_pv.yaml 'spec.csi.volumeHandle' "projects/${GOOGLE_PROJECT_ID}/zones/${GOOGLE_PROJECT_ZONE}/disks/${DISK}"
            yq d -i ${PVC}_pv.yaml 'spec.gcePersistentDisk'
            yq d -i ${PVC}_pv.yaml 'spec.claimRef.uid'
            yq d -i ${PVC}_pv.yaml 'spec.claimRef.resourceVersion'
            kubectl -n ${PFID} delete pvc ${PVC}
            kubectl -n ${PFID} delete pv ${PV}
            kubectl -n ${PFID} create -f ${PVC}_pv.yaml && rm ${PVC}_pv.yaml
        done
    else
        echo "[INFO] ES disk already in class ssd-retain-csi"
    fi
fi
