locals {
  host_project_id         = "akecld-prd-shared-infra"
  shared_vpc_name         = "akecld-prd-shared-infra-dev-xpn"
  shared_project_id       = "akecld-prd-pim-saas-shared"
  project_id              = "akecld-prd-pim-saas-dev"
  ci_sa                   = "main-service-account@${local.project_id}.iam.gserviceaccount.com"
  admins                  = ["group:ucs@akeneo.com"]
  viewers                 = ["group:ucs@akeneo.com"]
  firestore_projects_id   = ["akecld-prd-pim-fire-eur-dev", "akecld-prd-pim-fire-us-dev"]
  firestore_database_type = "CLOUD_FIRESTORE"
  dev_zone                = "pim-saas-dev.dev.cloud.akeneo.com"
  public_zone             = "ci.pim.akeneo.cloud"
  private_zone            = "pim-saas-dev.dev.local"
  ci_zone                 = "ci.pim.akeneo.cloud"
  shared_zone_name        = "pim-akeneo-cloud"
}

module "iam" {
  source                = "../modules/iam"
  project_id            = local.project_id
  firestore_projects_id = local.firestore_projects_id
  secrets_admins        = local.admins
  cloudbuild_admins     = local.admins
}

module "firestore_eur" {
  source        = "../modules/firestore"
  project_id    = "akecld-prd-pim-fire-eur-dev"
  location_id   = "europe-west"
  database_type = local.firestore_database_type
}

module "firestore_us" {
  source        = "../modules/firestore"
  project_id    = "akecld-prd-pim-fire-us-dev"
  location_id   = "us-central"
  database_type = local.firestore_database_type
}

module "secrets" {
  source     = "../modules/secrets"
  project_id = local.project_id
  secrets = [
    {
      name = "ARGOCD_USERNAME"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]

      labels = {
        usage = "argocd"
      }
    },
    {
      name = "ARGOCD_PASSWORD"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ],

      labels = {
        usage = "argocd"
      }
    },
    {
      name = "MAILER_API_KEY"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "timmy-tenant-provisioning"
      }
    },
    {
      name = "TIMMY_PORTAL"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "timmy-portal-auth"
      }
    },
    {
      name = "TENANT_CONTEXT_ENCRYPTION_KEY"
      members = [
        "serviceAccount:${module.iam.pim_sa_email}"
      ]
      labels = {
        usage = "tenant-context-encryption-key"
      }
    }
  ]
}

module "tenant_context_encryption_key" {
  source    = "../modules/tenant-context-secret"
  secret_id = module.secrets.google_secrets_ids["TENANT_CONTEXT_ENCRYPTION_KEY"]
}

module "gke_europe_west1" {
  source                 = "../modules/gke"
  project                = local.project_id
  host_project_id        = local.host_project_id
  shared_vpc_name        = local.shared_vpc_name
  gke_sa_email           = module.iam.gke_sa_email
  region                 = "europe-west1"
  cluster_developers     = local.admins
  viewer_members         = local.viewers
  admin_members          = local.admins
  min_master_version     = "1.23"
  master_ipv4_cidr_block = "192.168.192.128/28"

  node_pool_configs = {
    "default" = {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    },
    "mysql" = {
      name              = "mysql"
      preemptible       = false
      machine_type      = "n1-highmem-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    }
  }

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

module "gke_europe_west3" {
  source                 = "../modules/gke"
  project                = local.project_id
  host_project_id        = local.host_project_id
  shared_vpc_name        = local.shared_vpc_name
  gke_sa_email           = module.iam.gke_sa_email
  region                 = "europe-west3"
  cluster_developers     = local.admins
  viewer_members         = local.viewers
  admin_members          = local.admins
  min_master_version     = "1.23"
  master_ipv4_cidr_block = "192.168.193.64/28"

  node_pool_configs = {
    "default" = {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    },
    "mysql" = {
      name              = "mysql"
      preemptible       = false
      machine_type      = "n1-highmem-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    }
  }

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
      "europe-west3" = ["europe-west3-b"]
    }
  }
}

module "public_dns" {
  source     = "../modules/public-dns"
  project_id = local.project_id
  zone_name  = local.dev_zone
}

module "public_ci_dns" {
  source     = "../modules/public-dns"
  project_id = local.project_id
  zone_name  = local.ci_zone

  forward = {
    target_project_id = local.shared_project_id
    target_zone_name  = local.shared_zone_name
  }
}

module "private_dns" {
  source     = "../modules/private-dns"
  project_id = local.project_id
  zone_name  = local.private_zone
  networks   = []
  #networks   = [data.google_compute_network.vpc.id]
}

module "cloudarmor" {
  source     = "../modules/cloudarmor"
  project_id = local.project_id

  enable_rate_limiting_api = false
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-dev"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-dev"
  }
  required_version = "= 1.1.3"
}
