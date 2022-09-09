resource "google_storage_bucket" "this" {
  name                        = var.name
  project                     = var.project_id
  location                    = var.location
  force_destroy               = var.force_destroy
  uniform_bucket_level_access = var.uniform_bucket_level_access
  storage_class               = var.storage_class

  versioning {
    enabled = var.versioning
  }
}
