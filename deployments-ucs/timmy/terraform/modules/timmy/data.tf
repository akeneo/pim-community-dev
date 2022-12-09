data "google_compute_zones" "google_compute_zones" {
  project = var.project_id
  region  = var.region
}
