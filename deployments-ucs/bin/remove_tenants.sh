#!/bin/sh

if [[ ${GOOGLE_DOMAIN} == "" ]]; then
        echo "ERR : You must choose a Google domain to be able to call the argocd server"
        exit 9
fi

if [[ ${GOOGLE_CLUSTER_REGION} == "" ]]; then
        echo "ERR : You must choose a cluster region to be able to call the argocd server"
        exit 9
fi

TYPE=srnt
NS_LIST=$(kubectl get ns | grep Active | grep "${TYPE}-ucs\|${TYPE}-auto-ucs" | awk '{print $1}')

echo "${TYPE} Tenants list :"
echo "${NS_LIST}"

ARGOCD_PASSWORD=$(kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d)
kubectl config set-context --current --namespace=argocd
argocd login --core argocd-${GOOGLE_CLUSTER_REGION}.${GOOGLE_DOMAIN} --username admin --password ${ARGOCD_PASSWORD}

for TENANT in ${NS_LIST}; do
    NS_INFO=$(kubectl get ns | grep ${TENANT})
    TENANT=$(echo ${NS_INFO} | awk '{print $1}')
    NS_STATUS=$(echo ${NS_INFO} | awk '{print $2}')
    NS_AGE=$(echo ${NS_INFO} | awk '{print $3}')
    DELETE_TENANT=${FORCE_DELETE:-false}

    echo "-------------------------------------------"
    echo "Tenant : ${TENANT}"
    echo "  Status :                ${NS_STATUS}"
    echo "  Age :                   ${NS_AGE}"

    # Delete tenant environments that failed (not automatically removed by circleCI)
    # Theses environments are aged of 1 hour
    case "${NS_AGE}" in
        *d*) DELETE_TENANT=true ;;
        *h*) DELETE_TENANT=true ;;
        *) echo "  Tenant younger than 1 hour" ;;
    esac
    echo "  Marked for deletion :   ${DELETE_TENANT}"

    if [ ${DELETE_TENANT} = true ]; then
        echo "---[TODELETE] Tenant ${TENANT} with status ${NS_STATUS} since ${NS_AGE}"
        FUNCTION_NAME=${GOOGLE_REGION_PREFIX}-timmy-delete-tenant
        if [ ! -z "${SKIP_DEPLOY_TIMMY}" -a "${SKIP_DEPLOY_TIMMY}" = "false" ]; then
            FUNCTION_NAME=${GOOGLE_REGION_PREFIX}-${CIRCLE_BRANCH_LOWER}-timmy-delete-tenant
        fi
        JSON_BODY_STRING=$(printf "{\"branchName\":\"%s\",\"tenant_name\":\"%s\"}\n" ${CIRCLE_BRANCH} ${TENANT} )
        gcloud config unset auth/impersonate_service_account
        DELETE_TENANT_STATUS_CODE=0
        gcloud beta functions call ${FUNCTION_NAME} --region=${GOOGLE_CLUSTER_REGION}  --data  ${JSON_BODY_STRING} --gen2 || DELETE_TENANT_STATUS_CODE=1
        echo "      Timmy delete ${TENANT}"

        kubectl delete ns ${TENANT}
        exit ${DELETE_TENANT_STATUS_CODE}
    fi
done
