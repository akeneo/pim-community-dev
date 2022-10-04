resource "google_project_iam_custom_role" "pim_role" {
  project     = var.project_id
  role_id     = "pim.role"
  title       = "PIM Role"
  description = "Role for using PIM" # Set the same roles as configconnector, should be refine later
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
    "pubsub.subscriptions.consume",
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

resource "google_service_account" "pim_sa" {
  project      = var.project_id
  account_id   = "pim-saas-service"
  display_name = "PIM service account"
}

resource "google_project_iam_binding" "pim_sa_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.pim_role.name

  members = [
    "serviceAccount:${google_service_account.pim_sa.email}"
  ]
}

resource "google_project_iam_member" "pim_sa_firestore" {
  for_each = toset(var.firestore_projects_id)
  project  = each.value
  role     = "roles/firebaserules.system"
  member   = "serviceAccount:${google_service_account.pim_sa.email}"
}

resource "google_service_account" "pim_cloud_function_sa" {
  project      = var.project_id
  account_id   = "pim-cloud-function"
  display_name = "PIM cloud function service account"
}

resource "google_project_iam_member" "pim_cloud_function_sa_firestore" {
  for_each = toset(var.firestore_projects_id)
  project  = each.value
  role     = "roles/firebaserules.system"
  member   = "serviceAccount:${google_service_account.pim_cloud_function_sa.email}"
}

resource "google_project_iam_member" "pim_cloud_function_sa_pubsub" {
  project = var.project_id
  role    = "roles/pubsub.publisher"
  member  = "serviceAccount:${google_service_account.pim_cloud_function_sa.email}"
}

resource "google_service_account_iam_binding" "pim_cloud_function_sa_usage" {
  service_account_id = google_service_account.pim_cloud_function_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.configconnector.email}",
  ]
}
