#!/bin/bash
set -euo pipefail

# This script executes migrate an instance.
# Each major step is idempotent. If a step didn't finish successfully, you can re-run the script.
# It will not execute all the successful steps executed before the failing step (incremental progress).


function execute_step {
    local -r script_name="$1"

    if ! gsutil ls "gs://mig-ge-to-srnt/${SOURCE_PFID}/steps/$script_name.txt" ; then
      echo "$script_name step starting for PFID \"$SOURCE_PFID\""

      "$SCRIPT_PATH/migrate/$script_name.sh"
      date "+%Y%m%d%H%M%S" > "/tmp/$script_name.txt"
      gsutil cp "/tmp/$script_name.txt" "gs://mig-ge-to-srnt/${SOURCE_PFID}/steps/$script_name.txt"

      echo "$script_name step done for PFID \"$SOURCE_PFID\""
    else
      echo "$script_name step skipped because already done for PFID \"$SOURCE_PFID\""
    fi

}

if [[ -z "${SOURCE_PFID:-}" ]]; then
    echo "ERROR: environment variable SOURCE_PFID is not set." >&2
    exit 1
fi

if ! gsutil ls "gs://mig-ge-to-srnt/" >/dev/null ; then
    echo "Migration bucket \"mig-ge-to-srnt\" cannot be accessed."
    exit 1
fi;

SCRIPT_PATH=$(dirname "$(realpath -s "$0")")

echo "Starting migration for PFID \"$SOURCE_PFID\""

execute_step backup_data_from_instance
execute_step delete_instance
execute_step recreate_instance_srnt_infra
TARGET_PFID="${SOURCE_PFID}" execute_step restore_data

echo "Migration done for PFID \"$SOURCE_PFID\""

