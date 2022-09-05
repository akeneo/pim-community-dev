
resource "google_app_engine_application" "pim-firestore" {
  project       = var.project_id
  location_id   = var.location_id
  database_type = "CLOUD_FIRESTORE"
}
