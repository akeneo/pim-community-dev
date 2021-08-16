#!bin/bash
set -eo pipefail
set -x
BINDIR=$(dirname $(readlink -f $0))
PED_DIR="${BINDIR}/.."
kubeval=kubeval

K8S_CLUSTER_VERSION=${K8S_CLUSTER_VERSION:-}
WITH_ONBOARDER=${WITH_ONBOARDER:-0}

if [[ ! -f /usr/local/bin/kubeval ]]; then
    wget https://github.com/instrumenta/kubeval/releases/download/v0.16.1/kubeval-linux-386.tar.gz

    tar -xzf kubeval-linux-386.tar.gz
    mv kubeval /usr/local/bin/kubeval
    chmod +x /usr/local/bin/kubeval
fi

helm3 repo add akeneo-charts gs://akeneo-charts/
helm3 dependency update ${PED_DIR}/terraform/pim

if [[ -z $K8S_CLUSTER_VERSION ]]; then
    # Get the k8s version install on the current cluster
    K8S_MASTER_VERSION=$(kubectl version -o json | jq -r '.serverVersion.gitVersion' | sed -nre 's/^[^0-9]*(([0-9]+\.)*[0-9]+).*/\1/p')
else
    K8S_MASTER_VERSION=${K8S_CLUSTER_VERSION}
fi
if [[ ! -d v${K8S_MASTER_VERSION}-standalone ]]; then
    pip install openapi2jsonschema
    openapi2jsonschema -o "v${K8S_MASTER_VERSION}-standalone" \
      --kubernetes --stand-alone \
      --expanded https://raw.githubusercontent.com/kubernetes/kubernetes/v${K8S_MASTER_VERSION}/api/openapi-spec/swagger.json
fi
kubeval="${kubeval} --kubernetes-version ${K8S_MASTER_VERSION} --schema-location file://."

echo "Kubeval for k8s version ${K8S_MASTER_VERSION}"
yq d -i ${PED_DIR}/terraform/pim/values.yaml 'pim.jobs'
yq m -i -x ${PED_DIR}/terraform/pim/values.yaml ${PED_DIR}/terraform/pim/values-${TYPE}.yaml
echo "********************************************"
echo "*********  File values.yaml used  **********"
echo "********************************************"
cat ${PED_DIR}/terraform/pim/values.yaml
if (($WITH_ONBOARDER == 0)); then
    yq d ${PED_DIR}/config/fake-tf-helm-pim-values.yaml onboarder > ${PED_DIR}/config/fake-tf-helm-pim-values-without-onboarder.yaml
    helm3 template ${PED_DIR}/terraform/pim \
    -f ${PED_DIR}/config/ci-values.yaml \
    -f ${PED_DIR}/config/fake-tf-helm-pim-values-without-onboarder.yaml | ${kubeval}
else
    helm3 template ${PED_DIR}/terraform/pim \
    -f ${PED_DIR}/config/ci-values.yaml \
    -f ${PED_DIR}/config/fake-tf-helm-pim-values.yaml | ${kubeval}
fi
