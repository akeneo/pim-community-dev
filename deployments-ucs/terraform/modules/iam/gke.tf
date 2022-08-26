resource "google_service_account" "gke" {
  project      = var.project_id
  account_id   = "gke-sa"
  display_name = "GKE service account"
}

resource "google_project_iam_binding" "gke_metrics_binding" {
  project = var.project_id
  role    = "roles/monitoring.metricWriter"

  members = [
    "serviceAccount:${google_service_account.gke.email}",
  ]
}

resource "google_project_iam_binding" "gke_logging_binding" {
  project = var.project_id
  role    = "roles/logging.logWriter"

  members = [
    "serviceAccount:${google_service_account.gke.email}",
  ]
}