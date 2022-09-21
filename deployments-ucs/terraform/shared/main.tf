locals {
  project_id                   = "akecld-prd-pim-saas-shared"
  main_sa                      = "main-service-account@${local.project_id}.iam.gserviceaccount.com"
  ci_sa                        = "main-service-account@akecld-prd-pim-saas-dev.iam.gserviceaccount.com"
  admins                       = [
    "group:phoenix-squad@akeneo.com",
    "serviceAccount:ci-service-account@akecld-prd-pim-saas-dev.iam.gserviceaccount.com" # To be able to push image in the registry
  ]
  viewers                      = [
    "serviceAccount:argocd@akecld-prd-pim-saas-dev.iam.gserviceaccount.com",
    "serviceAccount:gke-sa@akecld-prd-pim-saas-dev.iam.gserviceaccount.com",
    "serviceAccount:cluster-bootstrap@akecld-prd-pim-saas-dev.iam.gserviceaccount.com",
  ]
  cloudbuild_github_repository = "pim-enterprise-dev"
  cloudbuild_github_branch     = "master"
  region                       = "europe-west1"
  multi_region                 = "EU"
}

module "registry_dev" {
  source               = "../modules/registry"
  project_id           = local.project_id
  admin_members        = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members       = concat(local.admins, ["serviceAccount:${local.ci_sa}"], local.viewers)
  registry_id          = "dev"
  registry_description = "Development registry"
  registry_region      = local.region
}

module "registry_prod" {
  source               = "../modules/registry"
  project_id           = local.project_id
  admin_members        = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members       = concat(local.admins, ["serviceAccount:${local.ci_sa}"], local.viewers)
  registry_id          = "prod"
  registry_description = "Prod registry"
  registry_region      = local.region
}

module "storage_bucket_dev" {
  source         = "../modules/storage-bucket"
  project_id     = local.project_id
  admin_members  = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members = concat(local.admins, ["serviceAccount:${local.ci_sa}"], local.viewers)
  bucket_name    = "akeneo-pim-saas-charts-dev"
  bucket_region  = local.multi_region
}

module "storage_bucket_prod" {
  source         = "../modules/storage-bucket"
  project_id     = local.project_id
  admin_members  = concat(local.admins, ["serviceAccount:${local.ci_sa}"])
  viewer_members = concat(local.admins, ["serviceAccount:${local.ci_sa}"], local.viewers)
  bucket_name    = "akeneo-pim-saas-charts-prod"
  bucket_region  = local.multi_region
}

module "cloud_build_infra_pim_saas_dev" {
  source                       = "../modules/cloudbuild"
  project_id                   = local.project_id
  trigger_name                 = "akecld-prd-pim-saas-dev"
  cloudbuild_filename          = ".cloudbuild/infra/akecld-prd-pim-saas-dev.yaml"
  cloudbuild_included_files    = [".cloudbuild/infra/akecld-prd-pim-saas-dev.yaml", "deployments-ucs/terraform/dev/**", "deployments-ucs/terraform/modules/**"]
  cloudbuild_github_repository = local.cloudbuild_github_repository
  cloudbuild_github_branch     = local.cloudbuild_github_branch
  cloudbuild_service_account   = local.main_sa
}

module "cloud_build_cluster_pim_saas_dev_europe_west1" {
  source                       = "../modules/cloudbuild"
  project_id                   = local.project_id
  trigger_name                 = "akecld-prd-pim-saas-dev-europe-west1"
  cloudbuild_filename          = ".cloudbuild/clusters/akecld-prd-pim-saas-dev-europe-west1.yaml"
  cloudbuild_included_files    = [".cloudbuild/clusters/akecld-prd-pim-saas-dev-europe-west1.yaml", "deployments-ucs/k8s/**", "deployments-ucs/k8s/**"]
  cloudbuild_github_repository = local.cloudbuild_github_repository
  cloudbuild_github_branch     = local.cloudbuild_github_branch
  cloudbuild_service_account   = local.main_sa
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-shared"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-shared"
  }
  required_version = "= 1.1.3"
}
