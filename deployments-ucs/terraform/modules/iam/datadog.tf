resource "google_secret_manager_secret" "datadog_api_key" {
  project   = var.project_id
  secret_id = "DATADOG_API_KEY"

  labels = {
    usage = "datadog"
  }

  replication {
    automatic = true
  }
}

resource "google_secret_manager_secret_iam_binding" "datadog_api_key_ci" {
  project   = var.project_id
  secret_id = google_secret_manager_secret.datadog_api_key.secret_id
  role      = "roles/secretmanager.secretAccessor"
  members   = [local.ci_sa]
}

resource "google_secret_manager_secret_iam_binding" "datadog_api_key_admins" {
  project   = var.project_id
  secret_id = google_secret_manager_secret.datadog_api_key.secret_id
  role      = "roles/secretmanager.secretVersionManager"
  members   = var.secrets_admins
}
