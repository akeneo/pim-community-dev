resource "google_service_account" "gke" {
  project      = var.project_id
  account_id   = "gke-sa"
  display_name = "GKE service account"
}
