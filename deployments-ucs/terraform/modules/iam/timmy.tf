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