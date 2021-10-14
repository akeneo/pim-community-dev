#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

# List of gcloud logging metrics in ${GOOGLE_PROJECT_ID}
LOGGING_METRICS_LIST_JSON=$(gcloud logging metrics list --project ${GOOGLE_PROJECT_ID} --format json)

COUNT_LOGGING_METRICS=0
for LOGGING_METRIC in $(echo ${LOGGING_METRICS_LIST_JSON} | jq -r '.[].name'); do
    METRIC_FILTER=$(echo ${LOGGING_METRICS_LIST_JSON} | jq -r ".[] | select(.name==\"${LOGGING_METRIC}\").filter")
    PFID_TMP=${METRIC_FILTER/*resource.labels.namespace_name=/}
    PFID=${PFID_TMP/ */}

    if [[ ${PFID} =~ ${NAMESPACE_REGEX_FILTER} ]] ; then
        NS_EXIST=$(echo ${KUBE_NS_LIST} | grep -w ${PFID} | wc -l)

        echo "-------------------------------------"
        echo "Logging metrics ${LOGGING_METRICS}"
        echo "  PFID          :   ${PFID}"
        echo "  NS exists     :   ${NS_EXIST}"

        if [[ ${NS_EXIST} -ge 1 ]]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
        fi

        echo "  Command debug"
        echo "      gcloud --quiet logging metrics delete ${LOGGING_METRIC} --project ${GOOGLE_PROJECT_ID} || true"
        gcloud --quiet logging metrics delete ${LOGGING_METRIC} --project ${GOOGLE_PROJECT_ID} || true

        ((COUNT_LOGGING_METRICS++))
    fi
done

echo "${COUNT_LOGGING_METRICS} logging metric(s) removed"
