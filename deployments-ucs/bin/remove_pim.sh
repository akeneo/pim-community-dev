#!/bin/sh

TYPE=pim
NS_LIST=$(kubectl get ns | grep Active | grep "${TYPE}-" | awk '{print $1}')

echo "${TYPE} PIM list :"
echo "${NS_LIST}"

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
            echo "      kubectl delete app ${PIM} -n argocd && kubectl delete ${PIM} || true"
            kubectl delete app ${PIM} -n argocd && kubectl delete ns ${PIM} || true
            ;;
            *) echo "  Command debug:"
            echo "      kubectl delete ${PIM} || true"
            kubectl delete ns ${PIM} || true
            ;;
        esac
    fi
done
