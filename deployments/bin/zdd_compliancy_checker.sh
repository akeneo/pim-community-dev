#!/bin/bash

# ZDD and non-ZDD Deployments
# https://www.notion.so/akeneo/ZDD-and-non-ZDD-Deployments-68513b4d5eb44970883875b75f6b0476
#
TYPE=${TYPE:-srnt}
RELEASE_BUCKET="serenity-edition"
ZCC_CONTEXT=${ZCC_CONTEXT:-artifact}

function downloadArtifacts() {
    # Get the oldest release to use as a starting point
    # SRNT
    CURRENT_TIME=$(date +%s)
    LAST_HOUR_TIME=$(( CURRENT_TIME - 60*60 ))
    OLDEST_RELEASE=$(curl --location -s -g -H "Content-Type: application/json" -H "DD-API-KEY: ${DATADOG_API_KEY}" -H "DD-APPLICATION-KEY: ${DATADOG_APP_KEY}" --request GET "https://api.datadoghq.eu/api/v1/query?from=${LAST_HOUR_TIME}&to=${CURRENT_TIME}&query=sum:kubernetes.containers.running{project:akecld-saas-prod,*,*,short_image:pim-enterprise-dev,app:pim,component:pim-web,*,type:${TYPE}}%20by%20{image_tag}" | jq -r .series[].tag_set[0] | sort | head -n1 | cut -c11-)

    if [[ ${TYPE} = "srnt" ]]; then
        echo "Get srnt target release"
        # Get the target release
        TARGET_RELEASE=$(git ls-remote --tags --sort="version:refname" git@github.com:akeneo/pim-enterprise-dev  | grep -oE 'v?[0-9]{14}$' | sort -dr | head -n1)
    else
        echo "Get grth target release"
        TARGET_RELEASE=$(gcloud container images list-tags eu.gcr.io/akeneo-cloud/pim-enterprise-dev --filter="growth-" --sort-by="~tags" --format="value(tags)" | head -n1)
        RELEASE_BUCKET="growth-edition"
    fi

    # Download the oldest release Docker image and Terraform modules
    rm -rf ~/zdd_compliancy_checker/
    mkdir -p ~/zdd_compliancy_checker/${OLDEST_RELEASE}/upgrades
    mkdir -p ~/zdd_compliancy_checker/${OLDEST_RELEASE}/deployments
    mkdir -p ~/zdd_compliancy_checker/${OLDEST_RELEASE}/dbschema
    docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${OLDEST_RELEASE}):/srv/pim/upgrades ~/zdd_compliancy_checker/${OLDEST_RELEASE}/upgrades
    if [[ ${TYPE} = "srnt" ]]; then
        docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${OLDEST_RELEASE}):/srv/pim/src/Akeneo/Tool/Bundle/DatabaseMetadataBundle/Resources/reference.pimdbschema.txt ~/zdd_compliancy_checker/${OLDEST_RELEASE}/dbschema/reference.pimdbschema.txt
    fi
    BOTO_CONFIG=/dev/null gsutil -m cp -r gs://akecld-terraform-modules/${RELEASE_BUCKET}/${OLDEST_RELEASE}/terraform/deployments/ ~/zdd_compliancy_checker/${OLDEST_RELEASE}/deployments

    # Download the target release Docker image and Terraform modules
    mkdir -p ~/zdd_compliancy_checker/${TARGET_RELEASE}/upgrades
    mkdir -p ~/zdd_compliancy_checker/${TARGET_RELEASE}/deployments
    mkdir -p ~/zdd_compliancy_checker/${TARGET_RELEASE}/dbschema
    docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${TARGET_RELEASE}):/srv/pim/upgrades ~/zdd_compliancy_checker/${TARGET_RELEASE}/upgrades
    if [[ ${TYPE} = "srnt" ]]; then
        docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${TARGET_RELEASE}):/srv/pim/src/Akeneo/Tool/Bundle/DatabaseMetadataBundle/Resources/reference.pimdbschema.txt ~/zdd_compliancy_checker/${TARGET_RELEASE}/dbschema/reference.pimdbschema.txt
    fi
    BOTO_CONFIG=/dev/null gsutil -m cp -r gs://akecld-terraform-modules/${RELEASE_BUCKET}/${TARGET_RELEASE}/terraform/deployments/ ~/zdd_compliancy_checker/${TARGET_RELEASE}/deployments
}

function getDiffType() {
  local DIFF=$1
  local CHAR=$(echo $DIFF | head -c 1)
  local TYPE="NONE"

  case $CHAR in
    "<")
      TYPE="DELETE"
      ;;

    ">")
      TYPE="ADD"
      ;;

    "-")
      TYPE="CONTINUE"
      ;;
  esac
  echo $TYPE
}

function getDiffTypeFromPreviousState() {
  local LINE=$1
  local PREVIOUS_TYPE=$2
  local CURRENT_TYPE=$(getDiffType "${LINE}")

  if [[ $PREVIOUS_TYPE == "CONTINUE" && $CURRENT_TYPE == "ADD" ]]; then
    CURRENT_TYPE="UPDATE"
  fi

  echo $CURRENT_TYPE
}

function getDiff() {
  local TARGETS=$1
  local DIFF=$(diff -r $TARGETS)
  local PREVIOUS_TYPE="NONE"
  local CACHE=""

  local ZDD=0

  while IFS= read -r LINE; do
    local CURRENT_TYPE=$(getDiffTypeFromPreviousState "${LINE}" ${PREVIOUS_TYPE})

    if [[ $CURRENT_TYPE != "NONE" && $CURRENT_TYPE != "CONTINUE" ]]; then
      LINE=$(echo $LINE | cut -c 3-)

      if [[ $CURRENT_TYPE == "UPDATE" ]]; then
        ZDD=1
        CACHE=""
      else
        if [[ $CACHE != "" ]]; then
          echo $CACHE
          CACHE=""
        fi
      fi

      if [[ $CURRENT_TYPE == "DELETE" ]]; then
        CACHE="${CURRENT_TYPE} : ${LINE}"
        ZDD=1
      else
        echo "${CURRENT_TYPE} : ${LINE}"
      fi

    fi
    PREVIOUS_TYPE=${CURRENT_TYPE}

  done <<< "$DIFF"

  echo $CACHE

  if [[ "$ZDD" == "1" ]]; then
    exit 1
  fi
}

case $ZCC_CONTEXT in
    "artifact")
        downloadArtifacts
        ;;

    "diff_infra")
        # Diff Docker images and Terraform modules
        echo -e "\n\n - Differences (infrastructure & migrations) between the oldest release in production & the next release to deploy :\n\n"
        diff -r ~/zdd_compliancy_checker/*/deployments/
        ;;

    "diff_db")
        TARGETS="$(realpath ~/zdd_compliancy_checker)/*/dbschema/"
        getDiff "${TARGETS}"
        exit $?
        ;;
esac
