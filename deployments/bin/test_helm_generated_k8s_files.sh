#!bin/bash
set -eo pipefail
set -x
BINDIR=$(dirname $(readlink -f $0))
PED_DIR="${BINDIR}/.."
kubeval=kubeval

K8S_CLUSTER_VERSION_ENABLED=${K8S_CLUSTER_VERSION_ENABLED:-0}
WITH_ONBOARDER=${WITH_ONBOARDER:-0}

if [[ ! -f /usr/local/bin/kubeval ]]; then
    wget https://github.com/instrumenta/kubeval/releases/download/0.15.0/kubeval-linux-386.tar.gz
    tar -xzf kubeval-linux-386.tar.gz
    mv kubeval /usr/local/bin/kubeval
    chmod +x /usr/local/bin/kubeval
fi

helm dependency update ${PED_DIR}/terraform/pim

if (($K8S_CLUSTER_VERSION_ENABLED == 1)); then
    K8S_MASTER_VERSION=$(kubectl version -o json | jq -r '.serverVersion.gitVersion' | sed -nre 's/^[^0-9]*(([0-9]+\.)*[0-9]+).*/\1/p')
    if [[ ! -d v${K8S_MASTER_VERSION}-standalone ]]; then
        pip install openapi2jsonschema
        openapi2jsonschema -o "v${K8S_MASTER_VERSION}-standalone" --kubernetes --stand-alone --expanded https://raw.githubusercontent.com/kubernetes/kubernetes/v${K8S_MASTER_VERSION}/api/openapi-spec/swagger.json
    fi
    kubeval="kubeval --kubernetes-version ${K8S_MASTER_VERSION}  --schema-location file://."
fi

if (($WITH_ONBOARDER == 0)); then
    yq d ${PED_DIR}/config/fake-tf-helm-pim-values.yaml onboarder >${PED_DIR}/config/fake-tf-helm-pim-values-without-onboarder.yaml
    helm template ${PED_DIR}/terraform/pim -f ${PED_DIR}/config/ci-values.yaml -f ${PED_DIR}/config/fake-tf-helm-pim-values-without-onboarder.yaml | ${kubeval}
else
    helm template ${PED_DIR}/terraform/pim -f ${PED_DIR}/config/ci-values.yaml -f ${PED_DIR}/config/fake-tf-helm-pim-values.yaml | ${kubeval}
fi
