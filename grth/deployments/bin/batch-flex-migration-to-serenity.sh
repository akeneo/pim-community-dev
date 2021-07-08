#!/usr/bin/env bash 
set +e

if [[ "${FLEXIBILITY_CUSTOMER_LIST}" == "" ]]; then
      echo "ERR : You must set FLEXIBILITY_CUSTOMER_LIST with the flexibility customer list path (customer name each line)"
      exit 9
fi

if [[ "${PED_TAG}" == "" ]]; then
      PED_TAG=$(git tag --list | grep -E "^v?[0-9]+$" | sort -r > /tmp/pim-tags.txt; head -n 1 /tmp/pim-tags.txt )
fi

if [[ "${GCLOUD_SERVICE_KEY}" == "" ]]; then
      echo "ERR : You must set GCLOUD_SERVICE_KEY with the content of the dedicated serviceaccount's key (by example GCLOUD_SERVICE_KEY=$(cat serviceaccout.json) )"
      exit 9
fi

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )
LOG_DIR=${SCRIPT_DIR}/../logs
mkdir -p ${LOG_DIR}
for line in $(cat "${FLEXIBILITY_CUSTOMER_LIST}"); do
      INSTANCE_NAME=clone-${line}  SOURCE_PFID=${line}  PED_TAG=${PED_TAG} PIM_CONTEXT=deployment  make clone_flexibility > ${LOG_DIR}/${line}-test_migrate.log 2>&1 || true 
      TYPE=srnt INSTANCE_NAME=clone-${line}  PIM_CONTEXT=deployment  make delete_clone_flexibility > ${LOG_DIR}/${line}-remove_test.log 2>&1  || true 
done
