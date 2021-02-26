#!/bin/bash

GOOGLE_PROJECT_ID=${GOOGLE_PROJECT_ID:-akecld-saas-dev}
GOOGLE_CLUSTER_ZONE=${GOOGLE_CLUSTER_ZONE:-europe-west3-a}

echo "Try to catch and remove unattached disks"

kubectl get pv -o json | jq -r '.items[] | select(.status | select(.phase=="Released")) | .spec.claimRef.namespace+" "+.metadata.name+" "+.spec.gcePersistentDisk.pdName' >/tmp/pv_list.txt
while read line; do
    pvinfo=($line)
    namespace=${pvinfo[0]}
    pvname=${pvinfo[1]}
    gkepd=${pvinfo[2]}

    #Check if tf state exists !
    if gsutil ls gs://akecld-terraform-dev/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${namespace} &>/dev/null; then
        echo "Big clean for ${pvname}"
        gcloud --project=${GOOGLE_PROJECT_ID} compute disks delete ${gkepd} --zone=${GOOGLE_CLUSTER_ZONE} --quiet &>/dev/null || true
        kubectl delete pv ${pvname}
        kubectl delete ns ${namespace} || true

    else
        echo "tfstate about ${namespace} exists, need manual cleaning"
        continue
    fi

done </tmp/pv_list.txt

echo "Try to catch and remove unattached policies and logging metrics"
gcloud components install alpha -q

gcloud beta logging metrics list --project ${GOOGLE_PROJECT_ID} --filter="name ~ srnt-pimup|srnt-pimci " --format="value(name)" >/tmp/metrics_list.txt
while read line; do
    namespace=$(echo ${line%%-login-response-time-distribution})
    if gsutil ls gs://akecld-terraform-dev/saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${namespace} &>/dev/null; then
        echo "Big clean for ${line}"
        relatedAlert=$(gcloud alpha monitoring policies list --project ${GOOGLE_PROJECT_ID} --filter="displayName ~ ${namespace}" --format="value(name)")
        if [[ $relatedAlert != "" ]]; then
            gcloud alpha monitoring policies delete -q --project ${GOOGLE_PROJECT_ID} ${relatedAlert}
        fi
        gcloud beta logging metrics delete --quiet --project ${GOOGLE_PROJECT_ID} ${line}
    else
        echo "tfstate about ${namespace} exists, need manual cleaning"
        continue
    fi

done </tmp/metrics_list.txt
