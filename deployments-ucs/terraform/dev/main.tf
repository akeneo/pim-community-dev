locals {
  project_id = "akecld-prd-pim-saas-dev"
  ci_sa      = "main-service-account@${local.project_id}.iam.gserviceaccount.com"
  admins     = ["group:phoenix-squad@akeneo.com"]
}

module "registry" {
  source          = "../modules/registry"
  project_id      = local.project_id
  admin_members   = concat(["serviceAccount:${local.ci_sa}"], local.admins)
  viewer_members  = []
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-dev"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-dev"
  }
  required_version = "= 1.1.3"
}