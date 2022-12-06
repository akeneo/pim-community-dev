resource "google_service_account" "datadog_gcp_integration" {
  project      = var.project_id
  account_id   = "gcp-datadog"
  display_name = "Datadog Google Cloud integration service account"
}

resource "google_project_iam_member" "datadog_compute_viewer" {
  project = var.project_id
  role    = "roles/compute.viewer"
  member  = "serviceAccount:${google_service_account.datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "datadog_monitoring_viewer" {
  project = var.project_id
  role    = "roles/monitoring.viewer"
  member  = "serviceAccount:${google_service_account.datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "datadog_cloudasset_viewer" {
  project = var.project_id
  role    = "roles/cloudasset.viewer"
  member  = "serviceAccount:${google_service_account.datadog_gcp_integration.email}"
}
