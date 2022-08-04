resource "google_service_account" "sa" {
  project      = var.project_id
  account_id   = var.account_id
  display_name = var.service_account_description
}

resource "google_project_iam_member" "sa_std_roles" {
  for_each = toset(["roles/logging.bucketWriter", "roles/logging.logWriter"])
  project  = var.project_id
  role     = each.value
  member   = "serviceAccount:${google_service_account.sa.email}"
}

resource "google_service_account_iam_binding" "sa_cloudbuild_binding" {
  service_account_id = google_service_account.sa.name
  role               = "roles/iam.serviceAccountTokenCreator"

  members = [
    "serviceAccount:service-${data.google_project.current.number}@gcp-sa-cloudbuild.iam.gserviceaccount.com",
  ]
}

resource "google_service_account_iam_binding" "sa_cloudbuild_admin_binding" {
  service_account_id = google_service_account.sa.name
  role               = "roles/iam.serviceAccountUser"

  members = var.admins
}

