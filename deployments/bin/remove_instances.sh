#!/bin/bash

# Check if TYPE is not empty
if [[ -z "${TYPE}" ]]; then
    echo "TYPE variable should not be empty"
    exit 1
fi

# Check if TYPE is in the list of corresponding list
if [[ ${TYPE} == "srnt" ]] || [[ ${TYPE} == "grth" ]] || [[ ${TYPE} == "tria" ]]; then
    echo "Stating removal for ${TYPE} environments"
else
    echo "TYPE (${TYPE}) variable is not a valid type"
    exit 1
fi

CONTAINER_FILTER="tags~^v[0-9]{14}$"
if [[ "${TYPE}" == "grth" ]]; then
    CONTAINER_FILTER="tags~^growth-v[0-9]{14}$"
fi
if [[ "${TYPE}" == "tria" ]]; then
    CONTAINER_FILTER="tags~^trial-v[0-9]{14}$"
fi

echo "Get last release version for ${TYPE}"
LAST_RELEASE=$(gcloud container images list-tags eu.gcr.io/akeneo-cloud/pim-enterprise-dev --filter="${CONTAINER_FILTER}" --sort-by="~tags" --format="value(tags)" | head -n1)

NS_LIST=${NS}
FORCE_DELETE=true
if [[ -z "${NS_LIST}" ]]; then
    # Namespaces are environments names, we remove only srnt-pimci* & srnt-pimup* & grth-pimci* & grth-pimup* & tria-pimci* environments
    NS_LIST=$(kubectl get ns | grep Active | egrep "${TYPE}-pim(ci|up)|${TYPE}-ge2ee-last" | awk '{print $1}')
    FORCE_DELETE=false
fi

echo "${TYPE} namespaces list :"
echo "${NS_LIST}"
if [[ ${TYPE} == "srnt" ]] ; then
    PRODUCT_REFERENCE_TYPE=serenity_instance
    PRODUCT_REFERENCE_CODE=serenity_${ENV_NAME}
    PRODUCT_TERRAFORM_BUCKET=serenity-edition
fi
if [[ ${TYPE} == "grth" ]] ; then
    PRODUCT_REFERENCE_TYPE=growth_edition_instance
    PRODUCT_REFERENCE_CODE=growth_edition_${ENV_NAME}
    PRODUCT_TERRAFORM_BUCKET=growth-edition
fi
if [[ ${TYPE} == "tria" ]] ; then
    PRODUCT_REFERENCE_TYPE=pim_trial_instance
    PRODUCT_REFERENCE_CODE=trial_${ENV_NAME}
    PRODUCT_TERRAFORM_BUCKET=trial-edition
fi

if [[ "${ENV_NAME}" == "dev" ]] ; then
    PRODUCT_TERRAFORM_BUCKET=${PRODUCT_TERRAFORM_BUCKET}-dev
fi

for NAMESPACE in ${NS_LIST}; do
    NS_INFO=($(kubectl get ns | grep ${NAMESPACE}))
    NAMESPACE=$(echo ${NS_INFO[0]})
    NS_STATUS=$(echo ${NS_INFO[1]})
    NS_AGE=$(echo ${NS_INFO[2]})
    DELETE_INSTANCE=${FORCE_DELETE:-false}

    echo "-------------------------------------------"
    echo "Namespace : ${NAMESPACE}"
    echo "  Status :                ${NS_STATUS}"
    echo "  Age :                   ${NS_AGE}"
    INSTANCE_NAME_PREFIX=pimci
    INSTANCE_NAME=$(echo ${NAMESPACE} | sed "s/${TYPE}-//")

    # delete environments that failed (not automatically removed by circleCI)
    # Theses environments are upgraded serenity / growth edition (pimup) and aged of 1 hour
    if [[ ${INSTANCE_NAME} == pimup* ]] ; then
        if [[ ${NS_AGE} == *h* ]] || [[ ${NS_AGE} == *d* ]] ; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimup
        elif [[ ${NS_AGE} == *m* ]] && ! ([[ ${NS_AGE} == *s* ]]) ; then
            NS_AGE=$(echo ${NS_AGE} | sed 's/m//')
            if [[ ${NS_AGE} -ge 60 ]] ; then
                DELETE_INSTANCE=true
                INSTANCE_NAME_PREFIX=pimup
            fi
        else
            echo "  NS younger than 1 hour"
        fi
    fi
    # Theses environments are test deploy duplicate serenity / growth edition (pimci-duplic) and aged of 1 hour
    if [[ ${INSTANCE_NAME} == pimci-duplic* ]] ; then
        if [[ ${NS_AGE} == *h* ]] || [[ ${NS_AGE} == *d* ]] ; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci-duplic
        elif [[ ${NS_AGE} == *m* ]] && ! ([[ ${NS_AGE} == *s* ]]) ; then
            NS_AGE=$(echo ${NS_AGE} | sed 's/m//')
            if [[ ${NS_AGE} -ge 60 ]] ; then
                DELETE_INSTANCE=true
                INSTANCE_NAME_PREFIX=pimci-duplic
            fi
        else
            echo "  NS younger than 1 hour"
        fi
    fi
    # Theses environments are cloned serenity / growth edition env with an upgrade from PR code and should be kept at least 30 days
    if [[ ${INSTANCE_NAME} == pimci-long-duplic* ]] ; then
        DEPLOY_TIME=$(helm3 list -n ${NAMESPACE} -adr --max 1 | grep ${NAMESPACE} | awk -F\\t '{print $4}' | awk '{print $1" "$2}')
        DAY_DIFF=$(( ($(date +%s) - $(date -d "${DEPLOY_TIME}" +%s)) / (60*60*24) ))
        echo "  Day diff :              ${DAY_DIFF}"
        if [[ -z "${DEPLOY_TIME}" ]] || [[ ${DAY_DIFF} -ge 30 ]]; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci-long-duplic
        else
            echo "  NS younger than 30 days"
        fi
    fi

    # Theses environments are test deploy serenity / growth edition (pimci) and aged of 1 hour
    if [[ ${INSTANCE_NAME} == pimci* ]] && ! ([[ ${INSTANCE_NAME} == pimci-pr* ]]) && ! ([[ ${INSTANCE_NAME} == pimci-*duplic* ]]) ; then
        if [[ ${NS_AGE} == *h* ]] || [[ ${NS_AGE} == *d* ]] ; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci
        elif [[ ${NS_AGE} == *m* ]] && ! ([[ ${NS_AGE} == *s* ]]) ; then
            NS_AGE=$(echo ${NS_AGE} | sed 's/m//')
            if [[ ${NS_AGE} -ge 60 ]] ; then
                DELETE_INSTANCE=true
                INSTANCE_NAME_PREFIX=pimci
            fi
        else
            echo "  NS younger than 1 hour"
        fi
    fi

    # Theses environments are deploy PR serenity / growth edition (pimci-pr) and aged of 1 day after the last deployment
    if [[ ${INSTANCE_NAME} == pimci-pr* ]] ; then
        DEPLOY_TIME=$(helm3 list -n ${NAMESPACE} -adr --max 1 | grep ${NAMESPACE} | awk -F\\t '{print $4}' | awk '{print $1" "$2}')
        DAY_DIFF=$(( ($(date +%s) - $(date -d "${DEPLOY_TIME}" +%s)) / (60*60*24) ))
        echo "  Day diff :              ${DAY_DIFF}"
        if [[ -z "${DEPLOY_TIME}" ]] || [[ ${DAY_DIFF} -ge 1 ]]; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=pimci-pr
        fi
    fi

        # Theses environments are related to ge2srnt project  and must not live more than 2 day
    if [[ ${INSTANCE_NAME} == ge2ee-last* ]] ; then
        DEPLOY_TIME=$(helm3 list -n ${NAMESPACE} -adr --max 1 | grep ${NAMESPACE} | awk -F\\t '{print $4}' | awk '{print $1" "$2}')
        DAY_DIFF=$(( ($(date +%s) - $(date -d "${DEPLOY_TIME}" +%s)) / (60*60*24) ))
        echo "  Day diff :              ${DAY_DIFF}"
        if [[ -z "${DEPLOY_TIME}" ]] || [[ ${DAY_DIFF} -ge 2 ]]; then
            DELETE_INSTANCE=true
            INSTANCE_NAME_PREFIX=ge2ee-last
        fi
    fi

    echo "  Marked for deletion :   ${DELETE_INSTANCE}"

    if [ $DELETE_INSTANCE = true ]; then
        echo "---[TODELETE] namespace ${NAMESPACE} with status ${NS_STATUS} since ${NS_AGE} (instance_name=${INSTANCE_NAME})"
        gsutil rm gs://akecld-terraform-dev/saas/akecld-saas-dev/europe-west3-a/${NAMESPACE}/default.tflock || true
        # retrive the image tag with the image from a pod
        POD=$(kubectl get pods --no-headers --namespace=${NAMESPACE} -l 'component in (pim-web,pim-daemon-job-consumer-process,pim-bigcommerce-connector-daemon)' | awk 'NR==1{print $1}')
        if [[ -z "$POD" ]]
        then
            # If no helm release exists then we can't delete helm/tf resources and we can delete namespace
            IMAGE_TAG=$(helm3 get values ${NAMESPACE} -n ${NAMESPACE} | yq r - 'image.pim.tag')
        else
            IMAGE=$(kubectl get pod --namespace=${NAMESPACE} -l 'component in (pim-daemon-job-consumer-process,pim-bigcommerce-connector-daemon)' -o json | jq -r '.items[0].status.containerStatuses[0].image')
            IMAGE_TAG=$(echo $IMAGE | grep -oP ':.*' | grep -oP '[^\:].*')
        fi

        if [[ -z "${IMAGE_TAG}" ]]; then
            IMAGE_TAG=${LAST_RELEASE}
        else
            # if no file has been pushed to terraform module bucket, we set image tag to last release
            TF_MODULE_EXIST=$(gsutil ls gs://akecld-terraform-modules/${PRODUCT_TERRAFORM_BUCKET}/${IMAGE_TAG})
            if [[ $? -ne 0 ]]; then
                echo "${IMAGE_TAG} files don't exist and IMAGE_TAG set to ${LAST_RELEASE}"
                IMAGE_TAG=${LAST_RELEASE}
            fi
        fi

        ACTIVATE_MONITORING=${ACTIVATE_MONITORING:-true}
        echo "  Command debug"
        echo "      ENV_NAME=${ENV_NAME} PRODUCT_REFERENCE_TYPE=${PRODUCT_REFERENCE_TYPE} PRODUCT_REFERENCE_CODE=${PRODUCT_REFERENCE_CODE} IMAGE_TAG=${IMAGE_TAG} TYPE=${TYPE} INSTANCE_NAME=${INSTANCE_NAME} INSTANCE_NAME_PREFIX=${INSTANCE_NAME_PREFIX} ACTIVATE_MONITORING=${ACTIVATE_MONITORING} make uncommit-instance || true"
        ENV_NAME=${ENV_NAME} PRODUCT_REFERENCE_TYPE=${PRODUCT_REFERENCE_TYPE} PRODUCT_REFERENCE_CODE=${PRODUCT_REFERENCE_CODE} IMAGE_TAG=${IMAGE_TAG} TYPE=${TYPE} INSTANCE_NAME=${INSTANCE_NAME} INSTANCE_NAME_PREFIX=${INSTANCE_NAME_PREFIX} ACTIVATE_MONITORING=${ACTIVATE_MONITORING} make uncommit-instance || true
    fi
done
