resource "google_service_account" "portal_function_sa" {
  project      = var.project_id
  account_id   = "timmy-portal-function"
  display_name = "Timmy portal request function"
}

resource "google_secret_manager_secret" "portal_auth" {
  project   = var.project_id
  secret_id = "PORTAL_TIMMY"

  labels = {
    usage = "timmy-portal-auth"
  }

  replication {
    automatic = true
  }
}

resource "google_secret_manager_secret_iam_binding" "portal_auth_binding" {
  project   = var.project_id
  secret_id = google_secret_manager_secret.portal_auth.secret_id
  role      = "roles/secretmanager.secretAccessor"
  members = [
    "serviceAccount:${google_service_account.portal_function_sa.email}",
  ]
}

resource "google_secret_manager_secret_iam_binding" "portal_auth_admin_binding" {
  project   = var.project_id
  secret_id = google_secret_manager_secret.portal_auth.secret_id
  role      = "roles/secretmanager.secretVersionManager"
  members   = var.secrets_admins
}

resource "google_service_account_iam_binding" "timmy_portal_sa_usage" {
  service_account_id = google_service_account.portal_function_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.crossplane.email}",
    "serviceAccount:ucs-crossplane-test-account@${var.project_id}.iam.gserviceaccount.com" ## To be removed
  ]
}