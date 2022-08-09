resource "google_service_account" "gke" {
  project      = var.project_id
  account_id   = "gke-${var.project_id}"
  display_name = "GKE service account"
}
