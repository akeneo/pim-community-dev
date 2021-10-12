#!/bin/bash

while ! (helm3 --namespace ${NS} history ${NS} | grep -q "pending-${PHASE}")
do
    sleep 60
done

date
helm3 --namespace ${NS} history ${NS}

sleep 30 # Give some time to helm for deployment creation
for i in $(kubectl get deployments -n ${NS} -o=jsonpath="{.items[*]['metadata.name']}")
do
    (kubectl -n ${NS} rollout status deployment ${i} | grep 'successfully rolled out' || (kubectl -n ${NS} logs deployments/${i})) &
done

for i in $(kubectl get statefulset -n ${NS} -o=jsonpath="{.items[*]['metadata.name']}")
do
    (kubectl -n ${NS} rollout status statefulset ${i} | grep 'statefulset rolling update complete' || (echo -e "\n### Statefulset ${i} failing ###\n" && kubectl -n ${NS} logs statefulset/${i})) &
done

while (helm3 --namespace ${NS} history ${NS} | grep -q "pending-${PHASE}")
do
    # Look for hooks near the backoffLimit
    FAILING_HOOKS=$(kubectl -n ${NS} get job -o json | jq -r '.items[] | select((.spec.backoffLimit - 1) <= .status.failed) | .metadata.name')
    if [ "${FAILING_HOOKS}" != "" ]; then
        for i in ${FAILING_HOOKS}
        do
            echo -e "\n### Hook ${i} failing ###\n"
            kubectl -n ${NS} logs -l "job-name=${i}" --max-log-requests 2
        done
        break
    fi
    sleep 5
done

date
helm3 --namespace ${NS} history ${NS}
