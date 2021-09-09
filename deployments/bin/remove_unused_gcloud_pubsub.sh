#!/bin/bash

GOOGLE_PROJECT_ID="${GOOGLE_PROJECT_ID:-akecld-saas-dev}"
GOOGLE_CLUSTER_ZONE="${GOOGLE_CLUSTER_ZONE:-europe-west3-a}"
SECONDS_PER_DAY=$((24*60*60))
MIN_AGE=1
NAMESPACE_REGEX_FILTER="^(grth|srnt|tria)-pim(ci|up)"

# List of current existing namespace in ${GOOGLE_PROJECT_ID}
KUBE_NS_LIST=$(kubectl get ns -o json | jq -r '.items[].metadata.name')

# List of gcloud physical disk with a description and no usage (users) in ${GOOGLE_PROJECT_ID}
PUBSUB_TOPIC_LIST_JSON=$(gcloud pubsub topics list --format json)
PUBSUB_SUBSCRIPTION_LIST_JSON=$(gcloud pubsub subscriptions list --format json)

COUNT_TOPIC=0
COUNT_SUBSCRIPTION=0
for PUBSUB_TOPIC in $(echo $PUBSUB_TOPIC_LIST_JSON | jq -r '.[].name'); do
    PFID=$(echo ${PUBSUB_TOPIC_LIST_JSON} | jq -r ".[] | select(.name==\"${PUBSUB_TOPIC}\").labels.pfid")
    
    if [[ ${PFID} =~ $NAMESPACE_REGEX_FILTER ]] ; then
        NS_EXIST=$(echo $KUBE_NS_LIST | grep ${PFID} | wc -l)
        
        echo "-------------------------------------"
        echo "Topic ${PUBSUB_TOPIC}"
        echo "  Namespace     :   ${PFID}"
        echo "  NS exists     :   ${NS_EXIST}"

        if [[ ${NS_EXIST} -eq 1 ]]; then
            echo "  Existing namespace : DELETION SKIPPED"
            continue
        fi

        CURRENT_SUBSCRIPTION=$(echo ${PUBSUB_SUBSCRIPTION_LIST_JSON} | jq -r ".[] | select(.topic==\"${PUBSUB_TOPIC}\").name")
        if [[ ${CURRENT_SUBSCRIPTION} == "" ]]; then
            echo "  Subscriptions :   NO"
        else
            echo "  Subscriptions :   ${CURRENT_SUBSCRIPTION}"
        fi

        echo "  Command debug"
        if [[ ${CURRENT_SUBSCRIPTION} != "" ]]; then
            echo "      gcloud pubsub subscriptions delete ${CURRENT_SUBSCRIPTION}"
        fi
        echo "      gcloud pubsub topics delete ${PUBSUB_TOPIC}"

        if [[ ${CURRENT_SUBSCRIPTION} != "" ]]; then
            gcloud pubsub subscriptions delete ${CURRENT_SUBSCRIPTION}
            ((COUNT_SUBSCRIPTION++))
        fi
        gcloud pubsub topics delete ${PUBSUB_TOPIC}

        ((COUNT_TOPIC++))
    fi
done

echo "${COUNT_TOPIC} topic(s) removed"
echo "${COUNT_SUBSCRIPTION} subscription(s) removed"
