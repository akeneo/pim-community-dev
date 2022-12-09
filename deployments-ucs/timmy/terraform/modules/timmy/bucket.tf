module "bucket" {
  source                      = "../bucket"
  location                    = var.bucket_location
  name                        = local.bucket_name
  project_id                  = var.project_id
  force_destroy               = true
  uniform_bucket_level_access = true
  versioning                  = true
}
