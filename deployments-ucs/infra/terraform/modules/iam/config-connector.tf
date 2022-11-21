resource "google_service_account" "configconnector" {
  project      = var.project_id
  account_id   = "config-connector"
  display_name = "Config Connector service account"
}

resource "google_project_iam_custom_role" "configconnector_role" {
  project     = var.project_id
  role_id     = "configconnector.role"
  title       = "Config Connector GKE Role"
  description = "Role for executing Config Connector in GKE"
  permissions = [
    "cloudbuild.builds.approve",
    "cloudbuild.builds.create",
    "cloudbuild.builds.get",
    "cloudbuild.builds.list",
    "cloudbuild.builds.update",

    "cloudfunctions.functions.call",
    "cloudfunctions.functions.create",
    "cloudfunctions.functions.delete",
    "cloudfunctions.functions.get",
    "cloudfunctions.functions.invoke",
    "cloudfunctions.functions.list",
    "cloudfunctions.functions.sourceCodeGet",
    "cloudfunctions.functions.sourceCodeSet",
    "cloudfunctions.functions.getIamPolicy",
    "cloudfunctions.functions.setIamPolicy",
    "cloudfunctions.functions.update",
    "cloudfunctions.runtimes.list",
    "cloudfunctions.locations.list",
    "cloudfunctions.operations.get",
    "cloudfunctions.operations.list",

    "cloudscheduler.jobs.create",
    "cloudscheduler.jobs.delete",
    "cloudscheduler.jobs.enable",
    "cloudscheduler.jobs.get",
    "cloudscheduler.jobs.update",
    "cloudscheduler.locations.get",

    "compute.disks.create",
    "compute.disks.delete",
    "compute.disks.get",
    "compute.disks.resize",
    "compute.disks.setLabels",
    "compute.instances.detachDisk",
    "compute.instances.get",
    "compute.zoneOperations.get",

    "datastore.entities.create",
    "datastore.entities.delete",
    "datastore.entities.get",
    "datastore.entities.update",

    "iam.serviceAccounts.create",
    "iam.serviceAccounts.delete",
    "iam.serviceAccounts.get",
    "iam.serviceAccounts.getIamPolicy",
    "iam.serviceAccounts.list",
    "iam.serviceAccounts.setIamPolicy",

    "pubsub.subscriptions.create",
    "pubsub.subscriptions.delete",
    "pubsub.subscriptions.get",
    "pubsub.topics.attachSubscription",
    "pubsub.topics.create",
    "pubsub.topics.delete",
    "pubsub.topics.get",

    "resourcemanager.projects.getIamPolicy",
    "resourcemanager.projects.setIamPolicy",

    "storage.buckets.create",
    "storage.buckets.delete",
    "storage.buckets.get",
    "storage.buckets.getIamPolicy",
    "storage.buckets.setIamPolicy",
    "storage.buckets.update",

    "storage.objects.get",
    "storage.objects.list",
    "storage.objects.create",
    "storage.objects.delete"
  ]
}

resource "google_project_iam_binding" "configconnector_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.configconnector_role.name

  members = [
    "serviceAccount:${google_service_account.configconnector.email}",
  ]
}

resource "google_project_iam_member" "configconnector_metrics_binding" {
  project = var.project_id
  role    = "roles/monitoring.metricWriter"
  member  = "serviceAccount:${google_service_account.configconnector.email}"
}

resource "google_project_iam_member" "configconnector_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[cnrm-system/cnrm-controller-manager]"
}
