EXIT_CODE=0
LAST_STATE=""

### WAIT FOR HELM INSTALL/UPGRADE START ###
while ! (helm3 --namespace ${NS} history ${NS} | grep -q "pending-${PHASE}")
do
sleep 10
done

date
helm3 --namespace ${NS} history ${NS}

### WAIT FOR DEPLOYMENTS ###
for i in $(kubectl get deployments -n ${NS} -o=jsonpath="{.items[*]['metadata.name']}")
do
    kubectl -n ${NS} rollout status deployment ${i} | grep 'successfully rolled out' || (kubectl -n ${NS} logs deployments/${i})
done

date
helm3 --namespace ${NS} history ${NS}

### WAIT FOR HOOKS ###
while (helm3 --namespace ${NS} history ${NS} | grep -q "pending-${PHASE}")
do
    HOOK_LIST=$(helm3 --namespace ${NS} status ${NS} -o json | jq -r '.hooks[] | select((.last_run.phase != "")) | [.name, .last_run.phase] | @tsv' | sort)
    comm -13 <(echo "${LAST_STATE}") <(echo "${HOOK_LIST}")
    LAST_STATE=${HOOK_LIST}
    sleep 5
done

### SHOW FAILED HOOKS ###
for i in $(helm3 status ${NS} -o json | jq -r '.hooks[] | select((.last_run.phase == "Failed")).name')
do
    kubectl logs -l "job-name=${i}" --namespace=${TYPE}-${INSTANCE_NAME}
done

date
helm3 --namespace ${NS} history ${NS}
