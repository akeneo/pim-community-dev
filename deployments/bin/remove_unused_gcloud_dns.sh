#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
SECONDS_PER_DAY=$((24*60*60))
MIN_AGE=1
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

# List of gcloud DNS starting with ((srnt|grth)-)?pim(ci|up)
# Ex : srnt-pimci-123-456 or pimup-7890123
DNS_LIST=$(gcloud --project=akeneo-cloud dns record-sets list --zone=dev-cloud-akeneo-com --filter="type=CNAME" --format json | jq -r '.[] | select(.name|test("((srnt|grth)-)?pim(ci|up)-[0-9a-z-A-Z]{7}-[0-9]{6}")) | .name')

COUNT_DNS=0
for DNS in ${DNS_LIST}; do
    DNS_PREFIX=$(echo ${DNS} | sed 's/\..*//')
    NS_EXIST=0

    if [[ ${KUBE_NS_LIST} =~ ${DNS_PREFIX} ]] ; then
        NS_EXIST=1
    fi

    echo "-------------------------------------"
    echo "DNS ${DNS}"
    echo "  DNS prefix     :   ${DNS_PREFIX}"
    echo "  NS exists     :   ${NS_EXIST}"

    if [[ ${NS_EXIST} -eq 1 ]]; then
        echo "  Existing namespace : DELETION SKIPPED"
        continue
    fi

    echo "  Command debug"
    echo "      gcloud --project=akeneo-cloud dns record-sets delete ${DNS} --type=CNAME --zone=dev-cloud-akeneo-com"

    gcloud --project=akeneo-cloud dns record-sets delete ${DNS} --type=CNAME --zone=dev-cloud-akeneo-com

    ((COUNT_DNS++))
done

echo "${COUNT_DNS} DNS removed"
