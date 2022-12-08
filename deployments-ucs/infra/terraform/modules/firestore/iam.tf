resource "google_service_account" "datadog_gcp_integration" {
  project      = var.project_id
  account_id   = "gcp-datadog"
  display_name = "Timmy Datadog Google Cloud integration service account"
}

resource "google_project_iam_member" "datadog_fire_compute_viewer" {
  project = var.project_id
  role    = "roles/compute.viewer"
  member  = "serviceAccount:${google_service_account.datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "datadog_fire_monitoring_viewer" {
  project = var.project_id
  role    = "roles/monitoring.viewer"
  member  = "serviceAccount:${google_service_account.datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "datadog_fire_cloudasset_viewer" {
  project = var.project_id
  role    = "roles/cloudasset.viewer"
  member  = "serviceAccount:${google_service_account.datadog_gcp_integration.email}"
}
