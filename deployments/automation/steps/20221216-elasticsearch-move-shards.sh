#!/bin/bash
set -eo pipefail

if [ ${STEP} == "PRE_APPLY" ]; then
    KUBECONFIG=.kubeconfig

    clientPodName=$(kubectl -n ${PFID} get pod -l 'app in (elasticsearch),component in (client)' -o 'jsonpath={.items[0].metadata.name}' 2>/dev/null)

    kubectl exec -n ${PFID} -it ${clientPodName} -- curl -XPUT 0:9200/_cluster/settings -H 'Content-Type: application/json' -d "{\"persistent\": {\"cluster.routing.allocation.exclude._name\": \"elasticsearch-client-*,elasticsearch-master-0,elasticsearch-master-1\"}}"
    while [ ! -z "$(kubectl exec -n ${PFID} -it ${clientPodName} -- curl -s 0:9200/_cat/shards | grep -e "elasticsearch-client" -e "elasticsearch-master" || true)" ]; do sleep 5; done
fi
