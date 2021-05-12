#!/bin/bash
set -eo pipefail
set -x

if [[ "${STEP}" == "" ]]; then
  echo "ERR : You must SET the STEP"
  exit 9
fi

UPGRADE_DIR=$(dirname $(readlink -f $0))
PRODUCT_TYPE=$(echo ${PWD##*/} | cut -d "-" -f 1)
TF_PATH_PIM_MODULE=${PWD}/.terraform/modules/pim/deployments/terraform
GOOGLE_PROJECT_ID=$(yq r -P ${PWD}/main.tf.json module.pim.google_project_id)
GOOGLE_PROJECT_ZONE=$(yq r -P ${PWD}/main.tf.json module.pim.google_project_zone)

echo "Start Upgrade ${STEP}"
if (declare -p INSTANCE_NAME &>/dev/null); then
  PFID="${PRODUCT_TYPE}-${INSTANCE_NAME}"
else
  PFID="${PRODUCT_TYPE}-$(yq r -P ${PWD}/main.tf.json module.pim.instance_name)"
fi

export PFID TF_PATH_PIM_MODULE GOOGLE_PROJECT_ID GOOGLE_PROJECT_ZONE

case "${STEP}" in
"PRE_INIT")
  echo "[INFO] ENTER in PRE_INIT step"
  for migration_script in $(ls -v ${UPGRADE_DIR}/steps/*.sh); do
    echo $(date) " [INFO][PRE_INIT] ${migration_script}"
    /bin/bash $migration_script
  done
  echo "[INFO] END OF PRE_INIT"
  ;;
"PRE_APPLY")
  echo "[INFO] ENTER in PRE_APPLY step"
  if [ ! -r ".kubeconfig" ]; then
    terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
  fi
  export KUBECONFIG=.kubeconfig
  for migration_script in $(ls -v ${UPGRADE_DIR}/steps/*.sh); do
    echo $(date) "[INFO][PRE_APPLY] ${migration_script}"
    /bin/bash $migration_script
  done
  echo "[INFO] END OF PRE_APPLY"
  ;;
"POST_APPLY")
  echo "[INFO] ENTER in POST_APPLY step"
  if [ ! -r ".kubeconfig" ]; then
    terraform apply -input=false -auto-approve -target=module.pim.local_file.kubeconfig
  fi
  export KUBECONFIG=.kubeconfig
  for migration_script in $(ls -v ${UPGRADE_DIR}/steps/*.sh); do
    echo $(date) "[INFO][POST_APPLY] ${migration_script}"
    /bin/bash $migration_script
  done
  echo "[INFO] END OF POST_APPLY"
  ;;
*)
  echo "[ERROR] Undefined STEP ${STEP}"
  exit 1
  ;;
esac
