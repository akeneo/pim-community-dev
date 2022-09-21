resource "google_storage_bucket" "helm_chart" {
  project       = var.project_id
  name          = var.bucket_name
  location      = var.bucket_region
  force_destroy = false

  uniform_bucket_level_access = true
}

resource "google_storage_bucket_iam_binding" "binding_admin_bucket" {
  bucket  = google_storage_bucket.helm_chart.name
  role    = "roles/storage.objectCreator"
  members = var.admin_members
}

resource "google_storage_bucket_iam_binding" "binding_viewer_bucket" {
  bucket  = google_storage_bucket.helm_chart.name
  role    = "roles/storage.objectViewer"
  members = var.viewer_members
}
