resource "google_project_iam_custom_role" "cloudconfig_role" {
  project     = var.project_id
  role_id     = "cloudconfig.role"
  title       = "Cloud Config GKE Role"
  description = "Role for executing Cloud Config in GKE"
  permissions = [
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
    "storage.buckets.get",
    "storage.objects.list",
    "storage.objects.create",
    "storage.objects.delete"
  ]
}

resource "google_service_account" "cloudconfig" {
  project      = var.project_id
  account_id   = "ucs-cloudconfig-account"
  display_name = "Cloud Config service account"
}

resource "google_project_iam_binding" "cloudconfig_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.cloudconfig_role.name

  members = [
    "serviceAccount:${google_service_account.cloudconfig.email}",
  ]
}

resource "google_project_iam_member" "cloudconfig_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[cnrm-system/cnrm-controller-manager]"
}
