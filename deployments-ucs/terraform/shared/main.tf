locals {
  project_id   = "akecld-prd-pim-saas-shared"
  ci_sa        = "main-service-account@akecld-prd-pim-saas-dev.iam.gserviceaccount.com"
  admins       = [
    "group:phoenix-squad@akeneo.com",
    "serviceAccount:ci-service-account@akecld-prd-pim-saas-dev.iam.gserviceaccount.com" # because of the makefile
  ]
  viewers      = [
    "group:phoenix-squad@akeneo.com",
    "serviceAccount:ci-service-account@akecld-prd-pim-saas-dev.iam.gserviceaccount.com", # because of the makefile

    "serviceAccount:gke-sa@akecld-prd-pim-saas-dev.iam.gserviceaccount.com",
    # "serviceAccount:gke-sa@akecld-prd-pim-saas-sandbox.iam.gserviceaccount.com",

    "serviceAccount:cluster-bootstrap@akecld-prd-pim-saas-dev.iam.gserviceaccount.com",
    # "serviceAccount:cluster-bootstrap@akecld-prd-pim-saas-sandbox.iam.gserviceaccount.com",
  ]
  region       = "europe-west1"
  multi_region = "EU"
}

module "registry_dev" {
  source               = "../modules/registry"
  project_id           = local.project_id
  admin_members        = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members       = concat(local.viewers)
  registry_id          = "dev"
  registry_description = "Development registry"
  registry_region      = local.region
}

module "registry_prod" {
  source               = "../modules/registry"
  project_id           = local.project_id
  admin_members        = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members       = concat(local.viewers)
  registry_id          = "prod"
  registry_description = "Prod registry"
  registry_region      = local.region
}

module "storage_bucket_dev" {
  source         = "../modules/storage-bucket"
  project_id     = local.project_id
  admin_members  = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members = concat(local.viewers)
  bucket_name    = "akeneo-pim-saas-charts-dev"
  bucket_region  = local.multi_region
}

module "storage_bucket_prod" {
  source         = "../modules/storage-bucket"
  project_id     = local.project_id
  admin_members  = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members = concat(local.viewers)
  bucket_name    = "akeneo-pim-saas-charts-prod"
  bucket_region  = local.multi_region
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-shared"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-shared"
  }
  required_version = "= 1.1.3"
}
