locals {
  host_project_id = "akecld-prd-shared-infra"
  shared_vpc_name = "akecld-prd-shared-infra-dev-xpn"
  project_id      = "akecld-prd-pim-saas-sandbox"
  ci_sa           = "main-service-account@${local.project_id}.iam.gserviceaccount.com"
  admins          = ["group:ucs@akeneo.com"]
  viewers         = ["group:ucs@akeneo.com"]
  regions         = ["europe-west1"]
  public_zone     = "pim-saas-sandbox.dev.cloud.akeneo.com"
  private_zone    = "pim-saas-sandbox.dev.local"
}

module "firestore-eur" {
  source      = "../modules/firestore"
  project_id  = "akecld-prd-pim-fire-eur-sand"
  location_id = "europe-west"
}

module "iam" {
  source            = "../modules/iam"
  project_id        = local.project_id
  secrets_admins    = local.admins
  cloudbuild_admins = local.admins
}

module "gke" {
  source                 = "../modules/gke"
  project                = local.project_id
  host_project_id        = local.host_project_id
  shared_vpc_name        = local.shared_vpc_name
  gke_sa_email           = module.iam.gke_sa_email
  regions                = local.regions
  cluster_developers     = concat(["serviceAccount:${local.ci_sa}"], local.admins)
  viewer_members         = local.viewers
  admin_members          = local.admins
  min_master_version     = "1.23.7"
  master_ipv4_cidr_block = "192.168.224.16/28"

  node_pool_configs = [
    {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    },
    {
      name              = "mysql"
      preemptible       = false
      machine_type      = "n1-highmem-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    },
  ]

  node_pool_labels = {
    "default" = {
      "node-type" = "default"
    },
    "mysql" = {
      component = "mysql",
      role      = "mysql-server"
    }
  }

  node_pools_taints = {
    default = [],
    mysql   = []
  }

  node_locations = {
    mysql = {
      "europe-west1" = ["europe-west1-b"]
    }
  }

}

module "public_dns" {
  source     = "../modules/public-dns"
  project_id = local.project_id
  zone_name  = local.public_zone
}

module "private_dns" {
  source     = "../modules/private-dns"
  project_id = local.project_id
  zone_name  = local.private_zone
  networks   = []
  #networks   = [data.google_compute_network.vpc.id]
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-sandbox"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-sandbox"
  }

  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "4.44.1"
    }
  }

  required_version = "1.1.3"
}
