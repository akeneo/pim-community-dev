#!/bin/sh

if [[ ${GOOGLE_DOMAIN} == "" ]]; then
        echo "ERR : You must choose a Google domain to be able to call the argocd server"
        exit 9
fi

if [[ ${GOOGLE_CLUSTER_REGION} == "" ]]; then
        echo "ERR : You must choose a cluster region to be able to call the argocd server"
        exit 9
fi

TYPE=pim
NS_LIST=$(kubectl get ns | grep Active | grep "${TYPE}-" | awk '{print $1}')

echo "${TYPE} PIM list :"
echo "${NS_LIST}"

ARGOCD_PASSWORD=$(kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d)
kubectl config set-context --current --namespace=argocd
argocd login --core argocd-${GOOGLE_CLUSTER_REGION}.${GOOGLE_DOMAIN} --username admin --password ${ARGOCD_PASSWORD}

for PIM in ${NS_LIST}; do
    NS_INFO=$(kubectl get ns | grep ${PIM})
    PIM=$(echo ${NS_INFO} | awk '{print $1}')
    NS_STATUS=$(echo ${NS_INFO} | awk '{print $2}')
    NS_AGE=$(echo ${NS_INFO} | awk '{print $3}')
    DELETE_PIM=${FORCE_DELETE:-false}

    echo "-------------------------------------------"
    echo "PIM : ${PIM}"
    echo "  Status :                ${NS_STATUS}"
    echo "  Age :                   ${NS_AGE}"

    # Delete PIM environments that failed (not automatically removed by circleCI)
    # Theses environments are aged of 1 hour
    case "${NS_AGE}" in
        *d*) DELETE_PIM=true ;;
        *h*) DELETE_PIM=true ;;
        *) echo "  PIM younger than 1 hour" ;;
    esac
    echo "  Marked for deletion :   ${DELETE_PIM}"

    if [ ${DELETE_PIM} = true ]; then
        echo "---[TODELETE] PIM ${PIM} with status ${NS_STATUS} since ${NS_AGE}"

        # Check if application exists
        APP_NAME=$(kubectl get application -n argocd | grep ${PIM} | awk '{print $1}')
        case "${APP_NAME}" in
            ${PIM}) echo "  Command debug:"
            echo "      argocd app terminate-op ${PIM} --core || true"
            echo "      kubectl delete app ${PIM} -n argocd"
            echo "      kubectl delete ${PIM}"
            argocd app terminate-op ${PIM} --core || true
            kubectl delete app ${PIM} -n argocd
            kubectl delete ns ${PIM}
            ;;
            *) echo "  Command debug:"
            echo "      kubectl delete ${PIM}"
            kubectl delete ns ${PIM}
            ;;
        esac
    fi
done
