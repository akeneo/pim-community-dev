#!/bin/bash
set -eo pipefail
set -x

if [[ "${STEP}" == "" ]]; then
  echo "ERR : You must SET the STEP"
  exit 9
fi

UPGRADE_DIR=$(dirname $(readlink -f $0))
TF_PATH_PIM_MODULE=${PWD}/.terraform/modules/pim/deployments/terraform
GOOGLE_PROJECT_ID=$(yq r -P ${PWD}/main.tf.json module.pim.google_project_id)
GOOGLE_PROJECT_ZONE=$(yq r -P ${PWD}/main.tf.json module.pim.google_project_zone)

echo "Start Upgrade ${STEP}"
if (declare -p INSTANCE_NAME &>/dev/null); then
  PFID="srnt-${INSTANCE_NAME}"
else
  PFID="srnt-$(yq r -P ${PWD}/main.tf.json module.pim.instance_name)"
fi

export PFID TF_PATH_PIM_MODULE GOOGLE_PROJECT_ID GOOGLE_PROJECT_ZONE

case "${STEP}" in
"PRE_INIT")
  echo "[INFO] ENTER in PRE_INIT step"
  for migration_script in $(ls -v ${UPGRADE_DIR}/steps/*.sh); do
    echo $(date) " [INFO][PRE_INIT] ${migration_script}"
    $migration_script
  done
  echo "[INFO] END OF PRE_INIT"
  ;;
"PRE_APPLY")
  echo "[INFO] ENTER in PRE_APPLY step"
  for migration_script in $(ls -v ${UPGRADE_DIR}/steps/*.sh); do
    echo $(date) "[INFO][PRE_APPLY] ${migration_script}"
    $migration_script
  done
  echo "[INFO] END OF PRE_APPLY"
  ;;
"POST_APPLY")
  echo "[INFO] ENTER in POST_APPLY step"
  for migration_script in $(ls -v ${UPGRADE_DIR}/steps/*.sh); do
    echo $(date) "[INFO][POST_APPLY] ${migration_script}"
    $migration_script
  done
  echo "[INFO] END OF POST_APPLY"
  ;;
*)
  echo "[ERROR] Undefined STEP ${STEP}"
  exit 1
  ;;
esac
