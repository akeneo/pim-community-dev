#!/bin/bash
set -euo pipefail

POSITIONAL_ARGS=()

while [[ $# -gt 0 ]]; do
  case $1 in
    -p|--project)
      GOOGLE_CLOUD_PROJECT="$2"
      shift # past argument
      shift # past value
      ;;
    -f|--firestore-project)
      GOOGLE_CLOUD_FIRESTORE_PROJECT="$2"
      shift # past argument
      shift # past value
      ;;
    -d|--domain)
      GOOGLE_DOMAIN="$2"
      shift # past argument
      shift # past value
      ;;
    -c|--cluster)
      GOOGLE_CLUSTER_NAME="$2"
      shift # past argument
      shift # past value
      ;;
    -r|--region)
      GOOGLE_CLUSTER_REGION="$2"
      shift # past argument
      shift # past value
      ;;
    -z|--zone)
      GOOGLE_ZONE="$2"
      shift # past argument
      shift # past value
      ;;
    -l|--location)
      LOCATION="$2"
      shift # past argument
      shift # past value
      ;;
    -x|--cluster-prefix)
      PREFIX_CLUSTER="$2"
      shift # past argument
      shift # past value
      ;;
    -*|--*)
      echo "Unknown option $1"
      exit 1
      ;;
    *)
      POSITIONAL_ARGS+=("$1") # save positional arg
      shift # past argument
      ;;
  esac
done

set -- "${POSITIONAL_ARGS[@]}" # restore positional parameters

echo "GOOGLE_CLOUD_PROJECT              = ${GOOGLE_CLOUD_PROJECT}"
echo "GOOGLE_CLOUD_FIRESTORE_PROJECT    = ${GOOGLE_CLOUD_FIRESTORE_PROJECT}"
echo "GOOGLE_DOMAIN                     = ${GOOGLE_DOMAIN}"
echo "GOOGLE_CLUSTER_NAME               = ${GOOGLE_CLUSTER_NAME}"
echo "GOOGLE_CLUSTER_REGION             = ${GOOGLE_CLUSTER_REGION}"
echo "GOOGLE_ZONE                       = ${GOOGLE_ZONE}"
echo "LOCATION                          = ${LOCATION}"
echo "PREFIX_CLUSTER                    = ${GOOGLE_ZONE}"

SCRIPT_FILE_PATH=$(realpath "$0")
SCRIPT_DIRECTORY_PATH=$(dirname "${SCRIPT_FILE_PATH}")
PIM_SAAS_SERVICE_DIRECTORY_PATH=$(realpath "${SCRIPT_DIRECTORY_PATH}/../pim-saas-service")

TENANT_CONTEXT="${GOOGLE_CLUSTER_REGION}/pim/tenant_contexts"
TOPIC_BUSINESS_EVENT="${PREFIX_CLUSTER}-srnt-business-event" # Should we really set the srnt in it?
TOPIC_JOB_QUEUE_UI="${PREFIX_CLUSTER}-srnt-job-queue-ui" # Should we really set the srnt in it?
TOPIC_JOB_QUEUE_IMPORT_EXPORT="${PREFIX_CLUSTER}-srnt-job-queue-import-export" # Should we really set the srnt in it?
TOPIC_JOB_QUEUE_DATA_MAINTENANCE="${PREFIX_CLUSTER}-srnt-job-queue-data-maintenance" # Should we really set the srnt in it?
TOPIC_JOB_QUEUE_SCHEDULED_JOB="${PREFIX_CLUSTER}-srnt-job-queue-scheduled-job" # Should we really set the srnt in it?
SUBSCRIPTION_WEBHOOK="${PREFIX_CLUSTER}-srnt-webhook" # Should we really set the srnt in it?
SUBSCRIPTION_JOB_QUEUE_UI="${PREFIX_CLUSTER}-srnt-job-queue-ui" # Should we really set the srnt in it?
SUBSCRIPTION_JOB_QUEUE_IMPORT_EXPORT="${PREFIX_CLUSTER}-srnt-job-queue-import-export" # Should we really set the srnt in it?
SUBSCRIPTION_JOB_QUEUE_DATA_MAINTENANCE="${PREFIX_CLUSTER}-srnt-job-queue-data-maintenance" # Should we really set the srnt in it?
SUBSCRIPTION_JOB_QUEUE_SCHEDULED_JOB="${PREFIX_CLUSTER}-srnt-job-queue-scheduled-job" # Should we really set the srnt in it?
CLOUD_FUNCTION_NAME="${PREFIX_CLUSTER}-srnt-job-publisher"
CLOUD_FUNCTION_BUCKET="${PREFIX_CLUSTER}-srnt-job-publisher"
CLOUD_SCHEDULER_PREFIX="${PREFIX_CLUSTER}-srnt-job-publisher"
CLOUD_FUNCTION_GSA="pim-cloud-function@${GOOGLE_CLOUD_PROJECT}.iam.gserviceaccount.com"
TENANT_CONTEXT_ENCRYPTION_ENABLED="true"
ENCRYPTION_KEY_SECRET_NAME="TENANT_CONTEXT_ENCRYPTION_KEY"

INSTANCE_NAME="ucs" # Should be removed
PIM_MASTER_DOMAIN="ucs.${GOOGLE_DOMAIN}" # Should be removed
WORKLOAD_IDENTITY_KSA="ksa-workload-identity" # Do we really need to change this value ? Or does the CI change it

## Charts
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/Chart.yaml appVersion ${RELEASE_NAME}

TENANT_NAME="ucs" # Should be removed
FQDN="ucs.${GOOGLE_DOMAIN}" # Should be removed
WORKLOAD_IDENTITY_KSA="ksa-workload-identity" # Do we really need to change this value ? Or does the CI change it

VALUES_FILE="${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml"

touch ${VALUES_FILE}
yq w -i "${VALUES_FILE}" global.extraLabels.tenant_name "${TENANT_NAME}"
yq w -i "${VALUES_FILE}" common.gcpFireStoreProjectID "${GOOGLE_CLOUD_FIRESTORE_PROJECT}"
yq w -i "${VALUES_FILE}" common.gcpProjectID "${GOOGLE_CLOUD_PROJECT}"
yq w -i "${VALUES_FILE}" common.googleZone "${GOOGLE_ZONE}"
yq w -i "${VALUES_FILE}" common.location "${LOCATION}"
yq w -i "${VALUES_FILE}" common.fqdn "${FQDN}"
yq w -i "${VALUES_FILE}" common.dnsCloudDomain "${GOOGLE_DOMAIN}"
yq w -i "${VALUES_FILE}" common.region "${GOOGLE_CLUSTER_REGION}"
yq w -i "${VALUES_FILE}" common.tenantContext "${TENANT_CONTEXT}"
yq w -i "${VALUES_FILE}" common.workloadIdentityKSA "${WORKLOAD_IDENTITY_KSA}"
yq w -i "${VALUES_FILE}" image.pim.tag "${RELEASE_NAME}"
yq w -i "${VALUES_FILE}" pim.pubsub.topicBusinessEvent "${TOPIC_BUSINESS_EVENT}"
yq w -i "${VALUES_FILE}" pim.pubsub.topicJobQueueUI "${TOPIC_JOB_QUEUE_UI}"
yq w -i "${VALUES_FILE}" pim.pubsub.topicJobQueueImportExport "${TOPIC_JOB_QUEUE_IMPORT_EXPORT}"
yq w -i "${VALUES_FILE}" pim.pubsub.topicJobQueueDataMaintenance "${TOPIC_JOB_QUEUE_DATA_MAINTENANCE}"
yq w -i "${VALUES_FILE}" pim.pubsub.topicJobQueueScheduledJob "${TOPIC_JOB_QUEUE_SCHEDULED_JOB}"
yq w -i "${VALUES_FILE}" pim.pubsub.subscriptionWebhook "${SUBSCRIPTION_WEBHOOK}"
yq w -i "${VALUES_FILE}" pim.pubsub.subscriptionJobQueueUI "${SUBSCRIPTION_JOB_QUEUE_UI}"
yq w -i "${VALUES_FILE}" pim.pubsub.subscriptionJobQueueImportExport "${SUBSCRIPTION_JOB_QUEUE_IMPORT_EXPORT}"
yq w -i "${VALUES_FILE}" pim.pubsub.subscriptionJobQueueDataMaintenance "${SUBSCRIPTION_JOB_QUEUE_DATA_MAINTENANCE}"
yq w -i "${VALUES_FILE}" pim.pubsub.subscriptionJobQueueScheduledJob "${SUBSCRIPTION_JOB_QUEUE_SCHEDULED_JOB}"
yq w -i "${VALUES_FILE}" pim.cloudFunction.name "${CLOUD_FUNCTION_NAME}"
yq w -i "${VALUES_FILE}" pim.cloudFunction.bucket "${CLOUD_FUNCTION_BUCKET}"
yq w -i "${VALUES_FILE}" pim.cloudFunction.serviceAccountEmail "${CLOUD_FUNCTION_GSA}"
yq w -i "${VALUES_FILE}" pim.jobsPrefix "${CLOUD_SCHEDULER_PREFIX}"
yq w -i "${VALUES_FILE}" tenantContextEncryption.enabled "${TENANT_CONTEXT_ENCRYPTION_ENABLED}"
yq w -i "${VALUES_FILE}" tenantContextEncryption.encryptionKeySecretName "${ENCRYPTION_KEY_SECRET_NAME}"
