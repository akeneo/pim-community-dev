resource "google_artifact_registry_repository" "pim_saas_repo" {
  provider      = google-beta
  project       = var.project_id
  location      = var.registry_region
  repository_id = "${var.project_id}-registry"
  description   = "PIM SaaS docker registry"
  format        = "DOCKER"
}

resource "google_artifact_registry_repository_iam_binding" "binding_admin" {
  provider   = google-beta
  project    = google_artifact_registry_repository.pim_saas_repo.project
  location   = google_artifact_registry_repository.pim_saas_repo.location
  repository = google_artifact_registry_repository.pim_saas_repo.name
  role       = "roles/artifactregistry.repoAdmin"
  members    = var.admin_members
}

resource "google_artifact_registry_repository_iam_binding" "binding_viewer" {
  provider   = google-beta
  project    = google_artifact_registry_repository.pim_saas_repo.project
  location   = google_artifact_registry_repository.pim_saas_repo.location
  repository = google_artifact_registry_repository.pim_saas_repo.name
  role       = "roles/artifactregistry.reader"
  members    = var.viewer_members
}