#!/bin/bash
set -euo pipefail

# This script executes migrate an instance.
# Each major step is idempotent. If a step didn't finish successfully, you can re-run the script.
# It will not execute all the successful steps executed before the failing step (incremental progress).


function execute_step {
    local -r script_name="$1"

    if ! gsutil ls "gs://mig-ge-to-srnt/${SOURCE_PFID}/steps/${script_name}.txt" ; then
      echo "$script_name step starting for PFID \"${SOURCE_PFID}\""

      bash "$SCRIPT_PATH/migrate/$script_name.sh"
      date "+%Y%m%d%H%M%S" > "/tmp/${script_name}.txt"
      gsutil cp "/tmp/${script_name}.txt" "gs://mig-ge-to-srnt/${SOURCE_PFID}/steps/${script_name}.txt"

      echo "${script_name} step done for PFID \"${SOURCE_PFID}\""
    else
      echo "${script_name} step skipped because already done for PFID \"${SOURCE_PFID}\""
    fi

}

if [[ ${DEVTEST:-false} == "true" ]]
    then ENV_NAME=dev
    else ENV_NAME=prod
fi

if [[ -z "${SOURCE_PFID:-}" ]]; then
    echo "ERROR: environment variable SOURCE_PFID is not set." >&2
    exit 1
fi
if [[ ! ${ENV_NAME:-} =~ ^(dev|prod)$ ]]; then
    echo "ERROR: environment variable ENV_NAME must be : dev or prod." >&2
    exit 1
fi
if [[ -z "${GOOGLE_PROJECT_ID:-}" ]]; then
    echo "ERROR: environment variable GOOGLE_PROJECT_ID is not set." >&2
    exit 1
fi
if [[ -z "${GOOGLE_CLUSTER_ZONE:-}" ]]; then
    echo "ERROR: environment variable GOOGLE_CLUSTER_ZONE is not set." >&2
    exit 1
fi
if ! gsutil ls "gs://mig-ge-to-srnt/" >/dev/null ; then
    echo "Migration bucket \"mig-ge-to-srnt\" cannot be accessed."
    exit 1
fi;

export DEVTEST
export SOURCE_PFID
export ENV_NAME
export GOOGLE_PROJECT_ID
export GOOGLE_CLUSTER_ZONE

#Caculated Vars
SCRIPT_PATH=$(dirname "$(realpath -s "$0")")
INSTANCE_NAME=$(echo "${SOURCE_PFID}" | cut -d "-" -f 2-)
TARGET_PFID="srnt-$INSTANCE_NAME"

export SCRIPT_PATH
export INSTANCE_NAME
export TARGET_PFID

#Print Vars
echo " --- Values for the migration --- "
echo "SCRIPT_PATH=${SCRIPT_PATH}"
echo "DEVTEST=${DEVTEST}"
echo " -------------------------------- "
echo "SOURCE_PFID=${SOURCE_PFID}"
echo "ENV_NAME=${ENV_NAME}"
echo "GOOGLE_PROJECT_ID=${GOOGLE_PROJECT_ID}"
echo "GOOGLE_CLUSTER_ZONE=${GOOGLE_CLUSTER_ZONE}"
echo " -------------------------------- "
echo "TARGET_PFID="${TARGET_PFID}""
echo ""
echo "Starting: $1  step for PFID: \"${SOURCE_PFID}\""
execute_step $1
echo "Step: $1 done for PFID: \"${SOURCE_PFID}\""
