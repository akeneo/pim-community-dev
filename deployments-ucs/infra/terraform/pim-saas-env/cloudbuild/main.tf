locals {
  pim_deployer_image = "europe-west1-docker.pkg.dev/akecld-prd-pim-saas-shared/prod/pim-deployer:1.3.0"
}

resource "google_storage_bucket" "cloudbuild_logs" {
  name          = "pim-saas-cloudbuild-logs-${var.env}"
  location      = "EU"
  force_destroy = true
  project       = var.cloudbuild_project_id

  uniform_bucket_level_access = true
}
