#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    # if the disk size is not in main.tf.json file, migration has not be done
    if (! grep -q "mysql_disk_size" "${PWD}/main.tf.json"); then

        #Get disk informations
        terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
        MYSQL_PD_NAME=$(
            export KUBECONFIG=.kubeconfig
            kubectl -n ${PFID} get pv $(kubectl -n ${PFID} get pvc data-mysql-server-0 -o jsonpath='{.spec.volumeName}') -o jsonpath='{.spec.gcePersistentDisk.pdName}'
        )
        MYSQL_SIZE=$(gcloud --project=${GOOGLE_PROJECT_ID} compute disks list --filter="name=\"${MYSQL_PD_NAME}\"" --limit=1 --format=json | jq -r '.[0]["sizeGb"]')
        DISK_SNAPSHOT=$(gcloud --project=${GOOGLE_PROJECT_ID} compute disks list --filter="name=\"${MYSQL_PD_NAME}\"" --limit=1 --format=json | jq -r '.[0]["sourceSnapshot"]')
        DISK_DESCRIPTION=$(gcloud --project=${GOOGLE_PROJECT_ID} compute disks list --filter="name=\"${MYSQL_PD_NAME}\"" --limit=1 --format=json | jq -r '.[0]["description"]')

        #apply in configuration file
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim.mysql_disk_size' "${MYSQL_SIZE}"
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim.mysql_disk_description' "${DISK_DESCRIPTION}"
        yq w -j -P -i ${PWD}/main.tf.json 'module.pim.mysql_disk_name' "${MYSQL_PD_NAME}"
        if [[ ${DISK_SNAPSHOT} != null ]]; then
            yq w -j -P -i ${PWD}/main.tf.json 'module.pim.mysql_source_snapshot' "${DISK_SNAPSHOT}"
        fi

        cat ${PWD}/main.tf.json

        #upgrade state file
        terraform import module.pim.google_compute_disk.mysql-disk ${GOOGLE_PROJECT_ID}/${GOOGLE_PROJECT_ZONE}/${MYSQL_PD_NAME}

        #remove useless information in helm configuration file
        yq delete -i ${PWD}/values.yaml 'mysql.mysql.dataDiskSize'
        yq delete -i ${PWD}/values.yaml 'mysql.common.persistentDisks'

        #delete pv/pvc, will be recreated by TF
        KUBECONFIG=.kubeconfig kubectl scale -n ${PFID} deploy/mysql --replicas=0 || true
        PV=$(KUBECONFIG=.kubeconfig kubectl get pv -n ${PFID} -o json | jq -r ".items[] | select ( (.spec.claimRef.name == \"data-mysql-server-0\") and .spec.claimRef.namespace==\"$PFID\") | .metadata.name")
        KUBECONFIG=.kubeconfig kubectl -n ${PFID} delete pvc data-mysql-server-0 || true
        KUBECONFIG=.kubeconfig kubectl -n ${PFID} delete pv ${PV}
        KUBECONFIG=.kubeconfig kubectl scale -n ${PFID} deploy/mysql --replicas=1 --timeout=0s || true

    else
        echo "[INFO] mysql disk already managed by terraform"
    fi
fi
