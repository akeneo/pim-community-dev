resource "google_service_account" "custom_metrics" {
  project      = var.project_id
  account_id   = "custom-metrics"
  display_name = "custom-metrics service account"
}

resource "google_project_iam_member" "custom_metrics_binding" {
  project = var.project_id
  role    = "roles/monitoring.viewer"
  member  = "serviceAccount:${google_service_account.custom_metrics.email}"
}

resource "google_project_iam_member" "custom_metrics_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[${var.custom_metrics_k8s_ns}/${var.custom_metrics_k8s_sa}]"
}
