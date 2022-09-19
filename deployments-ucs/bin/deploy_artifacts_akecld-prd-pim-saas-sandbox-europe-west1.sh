#!/bin/bash

GOOGLE_CLOUD_PROJECT="akecld-prd-pim-saas-sandbox"
GOOGLE_CLOUD_FIRESTORE_PROJECT="akecld-prd-pim-fire-eur-sand"
GOOGLE_DOMAIN="pim-saas-sand.dev.cloud.akeneo.com"
GOOGLE_CLUSTER_NAME="akecld-prd-pim-saas-sandbox-europe-west1"
GOOGLE_CLUSTER_REGION="europe-west1"
GOOGLE_ZONE="europe-west1-b"
LOCATION="EU"
PREFIX_CLUSTER="eur-w-1a"
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

INSTANCE_NAME="ucs" # Should be removed
PIM_MASTER_DOMAIN="ucs.${GOOGLE_DOMAIN}" # Should be removed
WORKLOAD_IDENTITY_KSA="ksa-workload-identity" # Do we really need to change this value ? Or does the CI change it


SCRIPT_FILE_PATH=$(realpath "$0")
SCRIPT_DIRECTORY_PATH=$(dirname "${SCRIPT_FILE_PATH}")
PIM_SAAS_SERVICE_DIRECTORY_PATH=$(realpath "${SCRIPT_DIRECTORY_PATH}/../pim-saas-service")

touch ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml global.extraLabels.instanceName "${INSTANCE_NAME}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.gcpFireStoreProjectID "${GOOGLE_CLOUD_FIRESTORE_PROJECT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.gcpProjectID "${GOOGLE_CLOUD_PROJECT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.googleZone "${GOOGLE_ZONE}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.location "${LOCATION}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.pimMasterDomain "${PIM_MASTER_DOMAIN}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.dnsCloudDomain "${GOOGLE_DOMAIN}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.region "${GOOGLE_CLUSTER_REGION}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.workloadIdentityKSA "${WORKLOAD_IDENTITY_KSA}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml image.pim.tag "${RELEASE_NAME}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topic_business_event "${TOPIC_BUSINESS_EVENT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topic_job_queue_ui "${TOPIC_JOB_QUEUE_UI}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topic_job_queue_import_export "${TOPIC_JOB_QUEUE_IMPORT_EXPORT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topic_job_queue_data_maintenance "${TOPIC_JOB_QUEUE_DATA_MAINTENANCE}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topic_job_queue_scheduled_job "${TOPIC_JOB_QUEUE_SCHEDULED_JOB}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscription_webhook "${SUBSCRIPTION_WEBHOOK}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscription_job_queue_ui "${SUBSCRIPTION_JOB_QUEUE_UI}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscription_job_queue_import_export "${SUBSCRIPTION_JOB_QUEUE_IMPORT_EXPORT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscription_job_queue_data_maintenance "${SUBSCRIPTION_JOB_QUEUE_DATA_MAINTENANCE}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscription_job_queue_scheduled_job "${SUBSCRIPTION_JOB_QUEUE_SCHEDULED_JOB}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.cloudFunction.name "${CLOUD_FUNCTION_NAME}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.cloudFunction.bucket "${CLOUD_FUNCTION_BUCKET}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.jobsPrefix "${CLOUD_SCHEDULER_PREFIX}"

## Charts
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/Chart.yaml appVersion ${RELEASE_NAME}
