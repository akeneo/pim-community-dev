#!/bin/sh

TYPE=srnt
NS_LIST=$(kubectl get ns | grep Active | grep "${TYPE}-ucs" | awk '{print $1}')

echo "${TYPE} Tenants list :"
echo "${NS_LIST}"

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

        # Check if application exists
        APP_NAME=$(kubectl get application -n argocd | grep ${TENANT} | awk '{print $1}')
        case "${APP_NAME}" in
            ${TENANT}) echo "  Command debug:"
            echo "      kubectl delete app ${TENANT} -n argocd && kubectl delete ${TENANT} || true"
            kubectl delete app ${TENANT} -n argocd && kubectl delete ns ${TENANT} || true
            ;;
            *) echo "  Command debug:"
            echo "      kubectl delete ${TENANT} || true"
            kubectl delete ns ${TENANT} || true
            ;;
        esac
    fi
done
