locals {
  host_project_id = "akecld-prd-shared-vpc-dev"
  shared_vpc_name = "akecld-prd-shared-vpc-dev-xpn"
  project_id      = "akecld-prd-pim-saas-dev"
  ci_sa           = "main-service-account@${local.project_id}.iam.gserviceaccount.com"
  admins          = ["group:phoenix-squad@akeneo.com"]
  viewers         = ["group:phoenix-squad@akeneo.com"]
  regions         = ["europe-west1"]
}

module "iam" {
  source         = "../modules/iam"
  project_id     = local.project_id
  secrets_admins = local.admins
}

module "registry" {
  source         = "../modules/registry"
  project_id     = local.project_id
  admin_members  = concat(["serviceAccount:${local.ci_sa}"], local.admins)
  viewer_members = concat(local.viewers, ["serviceAccount:${module.iam.gke_sa_email}"])
}

module "gke" {
  source             = "../modules/gke"
  project            = local.project_id
  host_project_id    = local.host_project_id
  shared_vpc_name    = local.shared_vpc_name
  gke_sa_email       = module.iam.gke_sa_email
  regions            = local.regions
  cluster_developers = concat(["serviceAccount:${local.ci_sa}"], local.admins)
  viewer_members     = local.viewers
  admin_members      = local.admins
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-dev"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-dev"
  }
  required_version = "= 1.1.3"
}
