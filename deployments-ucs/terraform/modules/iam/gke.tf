resource "google_service_account" "gke" {
  project      = var.project_id
  account_id   = "gke-sa"
  display_name = "GKE service account"
}

resource "google_project_iam_member" "gke_metrics_binding" {
  project = var.project_id
  role    = "roles/monitoring.metricWriter"
  member  = "serviceAccount:${google_service_account.gke.email}"
}

resource "google_project_iam_member" "gke_logging_binding" {
  project = var.project_id
  role    = "roles/logging.logWriter"
  member  = "serviceAccount:${google_service_account.gke.email}"
}
