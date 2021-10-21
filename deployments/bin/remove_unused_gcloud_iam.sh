#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
NAMESPACE_REGEX_FILTER="^pim(ci|up)"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

# List of gcloud iam service account in ${GOOGLE_PROJECT_ID}
IAM_SERVICE_ACCOUNTS_LIST_JSON=$(gcloud iam service-accounts list --project=${GOOGLE_PROJECT_ID} --format json )

COUNT_IAM_SERVICE_ACCOUNTS=0
for IAM_SERVICE_ACCOUNTS in $(echo $IAM_SERVICE_ACCOUNTS_LIST_JSON | jq -r '.[].email'); do
    PFID=$(echo ${IAM_SERVICE_ACCOUNTS_LIST_JSON} | jq -r ".[] | select(.email==\"${IAM_SERVICE_ACCOUNTS}\") | .displayName")
    PFID_TMP=${PFID/*\(/}
  	PFID=${PFID_TMP/)*}

    if [[ ${PFID} =~ ${NAMESPACE_REGEX_FILTER} ]] ; then
        NS_EXIST=$(echo ${KUBE_NS_LIST} | grep -w ${PFID} | wc -l)

        echo "-------------------------------------"
        echo "IAM ${IAM_SERVICE_ACCOUNTS}"
        echo "  PFID          :   ${PFID}"
        echo "  NS exists     :   ${NS_EXIST}"

        if [[ ${NS_EXIST} -ge 1 ]]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
        fi

        echo "  Command debug"
        echo "      gcloud --quiet iam service-accounts delete ${IAM_SERVICE_ACCOUNTS} --project=${GOOGLE_PROJECT_ID} || true"
        gcloud --quiet iam service-accounts delete ${IAM_SERVICE_ACCOUNTS} --project=${GOOGLE_PROJECT_ID} || true

        ((COUNT_IAM_SERVICE_ACCOUNTS++))
    fi
done

echo "${COUNT_IAM_SERVICE_ACCOUNTS} IAM(s) removed"
