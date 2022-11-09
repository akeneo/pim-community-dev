#!/bin/bash

GOOGLE_CLOUD_PROJECT="akecld-prd-pim-saas-dev"
GOOGLE_CLOUD_FIRESTORE_PROJECT="akecld-prd-pim-fire-eur-dev"
GOOGLE_DOMAIN="ci.pim.akeneo.cloud"
GOOGLE_CLUSTER_NAME="akecld-prd-pim-saas-dev-europe-west3"
GOOGLE_CLUSTER_REGION="europe-west3"
GOOGLE_ZONE="europe-west3-b"
LOCATION="EU"
PREFIX_CLUSTER="eur-w-3a"
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
TENANT_CONTEXT_ENCRYPTION_ENABLED="true"
ENCRYPTION_KEY_SECRET_NAME="TENANT_CONTEXT_ENCRYPTION_KEY"

TENANT_NAME="ucs" # Should be removed
FQDN="ucs.${GOOGLE_DOMAIN}" # Should be removed
WORKLOAD_IDENTITY_KSA="ksa-workload-identity" # Do we really need to change this value ? Or does the CI change it


SCRIPT_FILE_PATH=$(realpath "$0")
SCRIPT_DIRECTORY_PATH=$(dirname "${SCRIPT_FILE_PATH}")
PIM_SAAS_SERVICE_DIRECTORY_PATH=$(realpath "${SCRIPT_DIRECTORY_PATH}/../pim-saas-service")

touch ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml global.extraLabels.tenant_name "${TENANT_NAME}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.gcpFireStoreProjectID "${GOOGLE_CLOUD_FIRESTORE_PROJECT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.gcpProjectID "${GOOGLE_CLOUD_PROJECT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.googleZone "${GOOGLE_ZONE}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.location "${LOCATION}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.fqdn "${FQDN}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.dnsCloudDomain "${GOOGLE_DOMAIN}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.region "${GOOGLE_CLUSTER_REGION}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.tenantContext "${TENANT_CONTEXT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml common.workloadIdentityKSA "${WORKLOAD_IDENTITY_KSA}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml image.pim.tag "${RELEASE_NAME}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topicBusinessEvent "${TOPIC_BUSINESS_EVENT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topicJobQueueUI "${TOPIC_JOB_QUEUE_UI}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topicJobQueueImportExport "${TOPIC_JOB_QUEUE_IMPORT_EXPORT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topicJobQueueDataMaintenance "${TOPIC_JOB_QUEUE_DATA_MAINTENANCE}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.topicJobQueueScheduledJob "${TOPIC_JOB_QUEUE_SCHEDULED_JOB}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscriptionWebhook "${SUBSCRIPTION_WEBHOOK}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscriptionJobQueueUI "${SUBSCRIPTION_JOB_QUEUE_UI}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscriptionJobQueueImportExport "${SUBSCRIPTION_JOB_QUEUE_IMPORT_EXPORT}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscriptionJobQueueDataMaintenance "${SUBSCRIPTION_JOB_QUEUE_DATA_MAINTENANCE}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.pubsub.subscriptionJobQueueScheduledJob "${SUBSCRIPTION_JOB_QUEUE_SCHEDULED_JOB}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.cloudFunction.name "${CLOUD_FUNCTION_NAME}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.cloudFunction.bucket "${CLOUD_FUNCTION_BUCKET}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml pim.jobsPrefix "${CLOUD_SCHEDULER_PREFIX}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml tenantContextEncryption.enabled "${TENANT_CONTEXT_ENCRYPTION_ENABLED}"
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/values-${GOOGLE_CLUSTER_NAME}.yaml tenantContextEncryption.encryptionKeySecretName "${ENCRYPTION_KEY_SECRET_NAME}"

## Charts
yq w -i ${PIM_SAAS_SERVICE_DIRECTORY_PATH}/Chart.yaml appVersion ${RELEASE_NAME}
