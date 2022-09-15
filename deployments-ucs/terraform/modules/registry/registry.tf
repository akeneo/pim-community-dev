resource "google_artifact_registry_repository" "registry" {
  provider      = google-beta
  project       = var.project_id
  location      = var.registry_region
  repository_id = var.registry_id
  description   = var.registry_description
  format        = "DOCKER"
}

resource "google_artifact_registry_repository_iam_binding" "binding_admin_registry" {
  provider   = google-beta
  project    = google_artifact_registry_repository.registry.project
  location   = google_artifact_registry_repository.registry.location
  repository = google_artifact_registry_repository.registry.name
  role       = "roles/artifactregistry.repoAdmin"
  members    = var.admin_members
}

resource "google_artifact_registry_repository_iam_binding" "binding_viewer_registry" {
  provider   = google-beta
  project    = google_artifact_registry_repository.registry.project
  location   = google_artifact_registry_repository.registry.location
  repository = google_artifact_registry_repository.registry.name
  role       = "roles/artifactregistry.reader"
  members    = var.viewer_members
}
