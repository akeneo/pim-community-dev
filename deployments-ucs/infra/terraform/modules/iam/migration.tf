data "google_storage_transfer_project_service_account" "storage_transfer_project_service_account_destination" {
  project = var.project_id
}

resource "google_service_account" "migration_to_ucs" {
  project      = var.project_id
  account_id   = "migration-to-ucs"
  display_name = "Service account used to migrate on UCS cluster"
}

resource "google_project_iam_custom_role" "migration_admin_destination" {
  project     = var.project_id
  role_id     = "migration.admin.destination"
  title       = "UCS Migration Admin Destination Project"
  description = "Role needed for the SA to migrate the tenant from legacy to UCS"
  permissions = [
    "cloudbuild.builds.create",
    "cloudfunctions.functions.get",
    "run.routes.invoke",
    "container.clusters.get",
    "container.namespaces.get",
    "container.thirdPartyObjects.create",
    "container.thirdPartyObjects.get",
    "container.thirdPartyObjects.update",
    "logging.logEntries.create",
    "secretmanager.versions.access",
    "iam.serviceAccounts.getAccessToken",
    "iam.serviceAccounts.getOpenIdToken",
    "iam.serviceAccounts.actAs",
    "serviceusage.services.use",
    "storage.buckets.getIamPolicy",
    "storage.buckets.setIamPolicy",
    "storagetransfer.jobs.create",
    "storagetransfer.jobs.delete",
    "storagetransfer.jobs.run",
    "storagetransfer.operations.get",
    "storagetransfer.projects.getServiceAccount",
    "storage.buckets.get",
    "storage.buckets.get",
    "storage.buckets.get",
    "storage.buckets.get",
    "storage.buckets.get",
    "storage.buckets.get"
  ]
}

resource "google_project_iam_custom_role" "migration_admin_source" {
  for_each    = toset(var.source_project_id)
  project     = each.value
  role_id     = "migration.admin.source"
  title       = "UCS Migration Admin Source Project"
  description = "Role needed for the SA to migrate the tenant from legacy to UCS"
  permissions = [
    "compute.disks.createSnapshot",
    "compute.disks.list",
    "compute.snapshots.create",
    "compute.snapshots.get",
    "compute.snapshots.list",
    "compute.snapshots.setLabels",
    "compute.zoneOperations.get",
    "compute.disks.list",
    "compute.snapshots.get",
    "compute.snapshots.list",
    "compute.zoneOperations.get",
    "container.clusters.get",
    "container.clusters.list",
    "container.cronJobs.delete",
    "container.cronJobs.get",
    "container.cronJobs.list",
    "container.deployments.get",
    "container.deployments.update",
    "container.deployments.updateScale",
    "container.jobs.create",
    "container.jobs.delete",
    "container.jobs.get",
    "container.jobs.list",
    "container.namespaces.get",
  ]
}

resource "google_project_iam_custom_role" "migration_storage_transfer_destination" {
  project     = var.project_id
  role_id     = "migration.storagetransfer.destination"
  title       = "UCS Migration Storage Transfer Source"
  description = "Role needed for the storage transfer SA to migrate the buckets from legacy to UCS"
  permissions = [
    "storage.buckets.get",
    "storage.buckets.getIamPolicy",
    "storage.objects.create",
    "storage.objects.delete",
    "storage.objects.list"
  ]
}

resource "google_project_iam_custom_role" "migration_storage_transfer_source" {
  for_each    = toset(var.source_project_id)
  project     = each.value
  role_id     = "migration.storagetransfer.source"
  title       = "UCS Migration Storage Transfer Source"
  description = "Role needed for the storage transfer SA to migrate the buckets from legacy to UCS"
  permissions = [
    "storage.buckets.get",
    "storage.buckets.getIamPolicy",
    "storage.objects.get",
    "storage.objects.getIamPolicy",
    "storage.objects.list"
  ]
}

resource "google_project_iam_member" "migration_admin_destination_members" {
  project = var.project_id
  role    = google_project_iam_custom_role.migration_admin_destination.name

  member = "serviceAccount:${google_service_account.migration_to_ucs.email}"
}

resource "google_project_iam_member" "migration_admin_source_members" {
  for_each = toset(var.source_project_id)
  project  = each.value
  role     = google_project_iam_custom_role.migration_admin_source[each.key].name

  member = "serviceAccount:${google_service_account.migration_to_ucs.email}"
}

resource "google_project_iam_member" "migration_storage_transfer_destination_members" {
  project = var.project_id
  role    = google_project_iam_custom_role.migration_storage_transfer_destination.name

  member = "serviceAccount:${data.google_storage_transfer_project_service_account.storage_transfer_project_service_account_destination.email}"
}

resource "google_project_iam_member" "migration_storage_transfer_source_members" {
  for_each = toset(var.source_project_id)
  project  = each.value
  role     = google_project_iam_custom_role.migration_storage_transfer_source[each.key].name

  member = "serviceAccount:${data.google_storage_transfer_project_service_account.storage_transfer_project_service_account_destination.email}"
}
