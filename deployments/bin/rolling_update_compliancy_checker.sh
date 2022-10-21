#!/bin/bash

# ZDD and non-ZDD Deployments
# https://www.notion.so/akeneo/ZDD-and-non-ZDD-Deployments-68513b4d5eb44970883875b75f6b0476
#
TYPE=${TYPE:-srnt}
RELEASE_BUCKET="serenity-edition"
ZCC_CONTEXT=${ZCC_CONTEXT:-artifact}
VERSIONS_FILE=${VERSIONS_FILE:-"./rolling_update_versions.env"}
RELEASE_DIRECTORY="/tmp/releases"

COLOR_RED=$(echo -en '\e[31m')
COLOR_GREEN=$(echo -en '\e[32m')
COLOR_BLUE=$(echo -en '\e[34m')
COLOR_PURPLE=$(echo -en '\e[35m')
COLOR_RESTORE=$(echo -en '\e[0m')

CURRENT_TIME=$(date +%s)
LAST_HOUR_TIME=$(( CURRENT_TIME - 60*60 ))
SOURCE_RELEASE_TYPE="top"
SOURCE_RELEASE=""

OUTPUT="/dev/null"

function needs_arg() {
  if [ -z "$OPTARG" ]; then
    echo "No arg for --$OPT option";
    exit 2;
  fi;
}

function getTopReleaseVersion() {
    echo $(curl --location -s -g -H "Content-Type: application/json" -H "DD-API-KEY: ${DATADOG_API_KEY}" -H "DD-APPLICATION-KEY: ${DATADOG_APP_KEY}" --request GET "https://api.datadoghq.eu/api/v1/query?from=${LAST_HOUR_TIME}&to=${CURRENT_TIME}&query=top(sum:kubernetes.containers.running{project:akecld-saas-prod,*,*,short_image:pim-enterprise-dev,app:pim,component:pim-web,*,type:${TYPE}}%20by%20{image_tag},%201,%20%27max%27,%20%27desc%27)" | jq -r .series[0].tag_set[0] | cut -c11-)
}

function getOldestReleaseVersion() {
    echo $(curl --location -s -g -H "Content-Type: application/json" -H "DD-API-KEY: ${DATADOG_API_KEY}" -H "DD-APPLICATION-KEY: ${DATADOG_APP_KEY}" --request GET "https://api.datadoghq.eu/api/v1/query?from=${LAST_HOUR_TIME}&to=${CURRENT_TIME}&query=sum:kubernetes.containers.running{project:akecld-saas-prod,*,*,short_image:pim-enterprise-dev,app:pim,component:pim-web,*,type:${TYPE}}%20by%20{image_tag}" | jq -r .series[].tag_set[0] | sort | head -n1 | cut -c11-)
}

function getSourceRelease() {
  case $SOURCE_RELEASE_TYPE in
    "top")
      SOURCE_RELEASE=$(getTopReleaseVersion)
      ;;
    "oldest")
      SOURCE_RELEASE=$(getOldestReleaseVersion)
      ;;
	  *)
      echo "Unknown source release type"
      exit 1
      ;;
  esac
}

function downloadArtifacts() {
    if [[ ${TYPE} = "srnt" ]]; then
        echo "Get srnt target release"
        # Get the target release
        TARGET_RELEASE=$(gcloud container images list-tags eu.gcr.io/akeneo-cloud/pim-enterprise-dev --filter="tags~^v[0-9]{14}\$" --sort-by="~tags" --format="value(tags)" | head -n1)
    else
        echo "Get grth target release"
        TARGET_RELEASE=$(gcloud container images list-tags eu.gcr.io/akeneo-cloud/pim-enterprise-dev --filter="tags~^growth-v[0-9]{14}\$" --sort-by="~tags" --format="value(tags)" | head -n1)
        RELEASE_BUCKET="growth-edition"
    fi

    echo "source_release=${SOURCE_RELEASE}" > ${VERSIONS_FILE}
    echo "target_release=${TARGET_RELEASE}" >> ${VERSIONS_FILE}

    # Download the source release Docker image and Terraform modules
    rm -rf ${RELEASE_DIRECTORY}/
    mkdir -p ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/upgrades
    mkdir -p ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments
    mkdir -p ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/dbschema
    docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${SOURCE_RELEASE}):/srv/pim/upgrades ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/upgrades
    if [[ ${TYPE} = "srnt" ]]; then
        docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${SOURCE_RELEASE}):/srv/pim/src/Akeneo/Tool/Bundle/DatabaseMetadataBundle/Resources/reference.pimdbschema.txt ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/dbschema/reference.pimdbschema.txt
    fi
    BOTO_CONFIG=/dev/null gsutil -m cp -r gs://akecld-terraform-modules/${RELEASE_BUCKET}/${SOURCE_RELEASE}/deployments/ ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/asset
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/bin
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/cypress
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/instances
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/terraform/pim/templates/tests
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/test
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/Makefile
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/deployments/deployments/README.md
    rm -rf ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/upgrades/upgrades/test_schema
    rm ${RELEASE_DIRECTORY}/${SOURCE_RELEASE}/upgrades/upgrades/*

    # Download the target release Docker image and Terraform modules
    mkdir -p ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/upgrades
    mkdir -p ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments
    mkdir -p ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/dbschema
    docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${TARGET_RELEASE}):/srv/pim/upgrades ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/upgrades
    if [[ ${TYPE} = "srnt" ]]; then
        docker cp $(docker create --rm eu.gcr.io/akeneo-cloud/pim-enterprise-dev:${TARGET_RELEASE}):/srv/pim/src/Akeneo/Tool/Bundle/DatabaseMetadataBundle/Resources/reference.pimdbschema.txt ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/dbschema/reference.pimdbschema.txt
    fi
    BOTO_CONFIG=/dev/null gsutil -m cp -r gs://akecld-terraform-modules/${RELEASE_BUCKET}/${TARGET_RELEASE}/deployments/ ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/asset
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/bin
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/cypress
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/instances
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/terraform/pim/templates/tests
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/test
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/Makefile
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/deployments/deployments/README.md
    rm -rf ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/upgrades/upgrades/test_schema
    rm ${RELEASE_DIRECTORY}/${TARGET_RELEASE}/upgrades/upgrades/*
}

function getDiff() {
  local SOURCE=$1
  local TARGET=$2
  local DIFF_OUTPUT=$3

  local DIFF=$(diff -q -r "${SOURCE}" "${TARGET}")

  ESCAPED_TARGET_PATH=$(echo ${TARGET} | sed "s/\//\\\\\//g")

  while IFS= read -r LINE; do
    # Files differ between source and target
    if [[ ${LINE} =~ "Files " ]]; then
      FILE_SOURCE_PATH=$(echo "${LINE}" | cut -d ' ' -f2)
      FILE_TARGET_PATH=$(echo "${LINE}" | cut -d ' ' -f4)
      UPDATED_FILE=$(echo ${LINE} | cut -d " " -f 4)
      SDIFF=$(sdiff -s -B -W -E -Z ${FILE_SOURCE_PATH} ${FILE_TARGET_PATH} | sed -r $"s/(.*)(\s+[\<|]\t)(.*)/${COLOR_RED}\1${COLOR_RESTORE}\2\3/g" | sed -r $"s/(.*)(\s+[\>|]\t)(.*)/\1\2${COLOR_GREEN}\3${COLOR_RESTORE}/g")

      if [[ ! -z "${SDIFF}" ]]; then
        GIT_FILE_PATH=$(echo ${FILE_TARGET_PATH} | sed "s/${ESCAPED_TARGET_PATH}//g")
        COLOR=$(echo -en '\e[35m')
        echo ""
        echo "=================================================="
        echo "${COLOR_PURPLE}FILE : ${GIT_FILE_PATH}${COLOR_RESTORE}"
        echo "${COLOR_BLUE}https://github.com/akeneo/pim-enterprise-dev/blob/master${GIT_FILE_PATH}${COLOR_RESTORE}"
        echo "=================================================="

        if [[ ! -z "${DIFF_OUTPUT}" ]]; then
          echo "" > "${DIFF_OUTPUT}/$(basename ${GIT_FILE_PATH} .php)"
        fi

        echo -en "$SDIFF"
      fi
      continue
    fi

    # File has been added or deleted
    if [[ ${LINE} =~ "Only in " ]]; then
      FILE_PATH=$(echo "${LINE}" | grep -Eo '/.*' | sed 's/: /\//g' | sed 's/\/\//\//g')
      GIT_FILE_PATH=$(echo ${FILE_PATH} | sed "s/${ESCAPED_TARGET_PATH}//g")
      ACTION="ADDED"
      COLOR=${COLOR_GREEN}
      TYPE="FILE"
      if [[ -d "$FILE_PATH" ]];then
        TYPE="DIRECTORY"
      fi

      if [[ ${FILE_PATH} =~ ${SOURCE} ]]; then
        ACTION="DELETED"
        COLOR=${COLOR_RED}
      fi
      echo "=================================================="
      echo "${COLOR}${TYPE} ${ACTION}: ${GIT_FILE_PATH}${COLOR_RESTORE}"
      if [[ ${ACTION} == "ADDED" ]]; then
        echo "${COLOR_BLUE}https://github.com/akeneo/pim-enterprise-dev/blob/master${GIT_FILE_PATH}${COLOR_RESTORE}"

        if [[ ! -z "${DIFF_OUTPUT}" ]]; then
          echo "" > "${DIFF_OUTPUT}/$(basename ${GIT_FILE_PATH} .php)"
        fi
      fi
      echo "=================================================="

      continue
    fi
  done <<< "$DIFF"
}


# Get the options
while getopts "o-:" OPT; do
    if [ "$OPT" = "-" ]; then   # long option: reformulate OPT and OPTARG
        OPT="${OPTARG%%=*}"       # extract long option name
        OPTARG="${OPTARG#$OPT}"   # extract long option argument (may be empty)
        OPTARG="${OPTARG#=}"      # if long option argument, remove assigning `=`
    fi
    case $OPT in
        o) # display Help
            SOURCE_RELEASE_TYPE="oldest"
            ;;
        output)
            needs_arg
            OUTPUT="${OPTARG}"
            ;;
        \?) # Invalid option
            echo "Error: Invalid option"
            exit;;
  esac
done

case $ZCC_CONTEXT in
    "artifact")
        getSourceRelease
        downloadArtifacts
        cat ${VERSIONS_FILE}
        ;;

    "diff_infra")
        # Diff Docker images and Terraform modules
        echo -en "\n\n - Differences in infrastructure between the source release in production & the next release to deploy :\n\n"
        DIRECTORIES=$(file ${RELEASE_DIRECTORY}/* | grep directory | cut -d':' -f1 | sort)
        SOURCE=$(echo ${DIRECTORIES} | cut -d ' ' -f1)
        TARGET=$(echo ${DIRECTORIES} | cut -d ' ' -f2)
        getDiff "${SOURCE}/deployments" "${TARGET}/deployments"
        ;;

    "diff_db")
        echo -en "\n\n - Differences in dbschema between the source release in production & the next release to deploy :\n\n"
        DIRECTORIES=$(file ${RELEASE_DIRECTORY}/* | grep directory | cut -d':' -f1 | sort)
        SOURCE=$(echo ${DIRECTORIES} | cut -d ' ' -f1)
        TARGET=$(echo ${DIRECTORIES} | cut -d ' ' -f2)

        getDiff "${SOURCE}/dbschema" "${TARGET}/dbschema"

        echo -en "\n\n - Differences in upgrades between the source release in production & the next release to deploy :\n\n"
        getDiff "${SOURCE}/upgrades" "${TARGET}/upgrades" "${OUTPUT}"

        exit $?
        ;;
esac
