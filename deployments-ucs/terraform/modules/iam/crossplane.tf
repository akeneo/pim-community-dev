resource "google_project_iam_custom_role" "crossplane_role" {
  project     = var.project_id
  role_id     = "crossplane.role"
  title       = "Crossplane GKE Role"
  description = "Role for executing crossplane in GKE"
  permissions = [
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
    "storage.objects.list",
  ]
}

resource "google_service_account" "crossplane" {
  project      = var.project_id
  account_id   = "ucs-crossplane-account"
  display_name = "Crossplane service account"
}

resource "google_project_iam_binding" "crossplane_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.crossplane_role.name

  members = [
    "serviceAccount:${google_service_account.crossplane.email}",
  ]
}

resource "google_project_iam_member" "crossplane_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[${var.crossplane_k8s_ns}/${var.crossplane_k8s_sa}]"
}

