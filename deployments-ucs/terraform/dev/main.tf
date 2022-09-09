locals {
  host_project_id      = "akecld-prd-shared-infra"
  shared_vpc_name      = "akecld-prd-shared-infra-dev-xpn"
  project_id           = "akecld-prd-pim-saas-dev"
  firestore_project_id = "akecld-prd-pim-fire-eur-dev"
  ci_sa                = "main-service-account@${local.project_id}.iam.gserviceaccount.com"
  admins               = ["group:phoenix-squad@akeneo.com"]
  viewers              = ["group:phoenix-squad@akeneo.com"]
  regions              = ["europe-west1"]
  public_zone          = "pim-saas-dev.dev.cloud.akeneo.com"
  private_zone         = "pim-saas-dev.dev.local"
}

module "iam" {
  source               = "../modules/iam"
  project_id           = local.project_id
  firestore_project_id = local.firestore_project_id
  secrets_admins       = local.admins
  cloudbuild_admins    = local.admins
}

module "firestore-eur" {
  source      = "../modules/firestore"
  project_id  = "akecld-prd-pim-fire-eur-dev"
  location_id = "europe-west"
}

module "firestore-us" {
  source      = "../modules/firestore"
  project_id  = "akecld-prd-pim-fire-us-dev"
  location_id = "us-central"
}

module "secrets" {
  source     = "../modules/secrets"
  project_id = local.project_id
  secrets    = [
    {
      name    = "ARGOCD_USERNAME"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]

      labels = {
        usage = "argocd"
      }
    },
    {
      name    = "ARGOCD_PASSWORD"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ],

      labels = {
        usage = "argocd"
      }
    },
    {
      name    = "MAILER_API_KEY"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "timmy-tenant-provisioning"
      }
    },
    {
      name    = "TIMMY_PORTAL"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "timmy-portal-auth"
      }
    }
  ]
}

module "registry" {
  source         = "../modules/registry"
  project_id     = local.project_id
  admin_members  = concat(["serviceAccount:${local.ci_sa}"], local.admins)
  viewer_members = concat(local.viewers, ["serviceAccount:${module.iam.gke_sa_email}", "serviceAccount:${module.iam.cluster_bootstrap_sa_email}"])
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
  master_ipv4_cidr_block = "192.168.224.0/28"

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
    bucket = "akecld-terraform-pim-saas-dev"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-dev"
  }
  required_version = "= 1.1.3"
}
