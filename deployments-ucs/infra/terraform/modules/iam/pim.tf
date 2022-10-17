resource "google_service_account" "pim_sa" {
  project      = var.project_id
  account_id   = "pim-saas-service"
  display_name = "PIM service account"
}

resource "google_project_iam_custom_role" "pim_role" {
  project     = var.project_id
  role_id     = "pim.role"
  title       = "PIM Role"
  description = "Role for using PIM"
  permissions = [
    "pubsub.subscriptions.get",
    "pubsub.subscriptions.consume",
    "pubsub.topics.get",
    "pubsub.topics.publish",

    "storage.objects.get",
    "storage.objects.list",
    "storage.objects.create",
    "storage.objects.delete"
  ]
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

# Service account for cloud function
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
