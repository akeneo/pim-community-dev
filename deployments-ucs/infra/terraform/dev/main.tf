locals {
  env                     = "dev"
  env_shorted             = "dev"
  host_project_id         = "akecld-prd-shared-infra"
  shared_vpc_name         = "akecld-prd-shared-infra-dev-xpn"
  shared_project_id       = "akecld-prd-pim-saas-shared"
  project_id              = "akecld-prd-pim-saas-dev"
  project_name            = "pim-saas-dev"
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
  datadog_api_key         = data.google_secret_manager_secret_version.datadog_api_key.secret_data
  datadog_app_key         = data.google_secret_manager_secret_version.datadog_app_key.secret_data
}

data "google_secret_manager_secret_version" "datadog_api_key" {
  secret  = "DATADOG_API_KEY"
  project = local.shared_project_id
}

data "google_secret_manager_secret_version" "datadog_app_key" {
  secret  = "DATADOG_APP_KEY"
  project = local.shared_project_id
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
      name = "EUROPE_WEST1_ARGOCD_PASSWORD"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ],

      labels = {
        usage  = "argocd"
        region = "europe-west1"
      }
    },
    {
      name = "EUROPE_WEST3_ARGOCD_PASSWORD"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ],

      labels = {
        usage  = "argocd"
        region = "europe-west3"
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
  name                   = "${local.project_id}-europe-west1"
  host_project_id        = local.host_project_id
  shared_vpc_name        = local.shared_vpc_name
  gke_sa_email           = module.iam.gke_sa_email
  region                 = "europe-west1"
  cluster_developers     = local.admins
  viewer_members         = local.viewers
  admin_members          = local.admins
  min_master_version     = "1.24"
  master_ipv4_cidr_block = "192.168.192.128/28"

  node_pool_configs = {
    "default" = {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-8"
      min_node_count    = 0
      max_pods_per_node = 64
    },
    "mysql" = {
      name              = "mysql"
      preemptible       = false
      machine_type      = "n1-highmem-8"
      min_node_count    = 0
      max_pods_per_node = 64
    },
    "elasticsearch" = {
      name              = "elasticsearch"
      preemptible       = false
      machine_type      = "n1-highmem-8"
      min_node_count    = 0
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
    },
    "elasticsearch" = {
      component = "elasticsearch",
      role      = "elasticsearch"
    }
  }

  node_pools_taints = {
    default = [],
    mysql = [
      {
        key    = "component"
        value  = "mysql"
        effect = "NO_EXECUTE"
      }
    ],
    elasticsearch = [
      {
        key    = "component"
        value  = "elasticsearch"
        effect = "NO_EXECUTE"
      }
    ]
  }
}

module "gke_europe_west3" {
  source                 = "../modules/gke"
  project                = local.project_id
  name                   = "${local.project_id}-europe-west3"
  host_project_id        = local.host_project_id
  shared_vpc_name        = local.shared_vpc_name
  gke_sa_email           = module.iam.gke_sa_email
  region                 = "europe-west3"
  cluster_developers     = local.admins
  viewer_members         = local.viewers
  admin_members          = local.admins
  min_master_version     = "1.24"
  master_ipv4_cidr_block = "192.168.193.64/28"

  node_pool_configs = {
    "default" = {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-8"
      min_node_count    = 0
      max_pods_per_node = 64
    },
    "mysql" = {
      name              = "mysql"
      preemptible       = false
      machine_type      = "n1-highmem-8"
      min_node_count    = 0
      max_pods_per_node = 64
    },
    "elasticsearch" = {
      name              = "elasticsearch"
      preemptible       = false
      machine_type      = "n1-highmem-8"
      min_node_count    = 0
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
    },
    "elasticsearch" = {
      component = "elasticsearch",
      role      = "elasticsearch"
    }
  }

  node_pools_taints = {
    default = [],
    mysql = [
      {
        key    = "component"
        value  = "mysql"
        effect = "NO_EXECUTE"
      }
    ],
    elasticsearch = [
      {
        key    = "component"
        value  = "elasticsearch"
        effect = "NO_EXECUTE"
      }
    ],
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

module "timmy_datadog" {
  source                        = "../modules/datadog"
  project_id                    = local.project_id
  datadog_api_key               = local.datadog_api_key
  datadog_app_key               = local.datadog_app_key
  datadog_gcp_integration_id    = module.iam.datadog_gcp_integration_id
  datadog_gcp_integration_email = module.iam.datadog_gcp_integration_email
}

module "timmy_datadog_firestore_eur" {
  source                        = "../modules/datadog"
  project_id                    = "akecld-prd-pim-fire-eur-dev"
  datadog_api_key               = local.datadog_api_key
  datadog_app_key               = local.datadog_app_key
  datadog_gcp_integration_id    = module.firestore_eur.datadog_gcp_integration_id
  datadog_gcp_integration_email = module.firestore_eur.datadog_gcp_integration_email
}

module "timmy_datadog_firestore_us" {
  source                        = "../modules/datadog"
  project_id                    = "akecld-prd-pim-fire-us-dev"
  datadog_api_key               = local.datadog_api_key
  datadog_app_key               = local.datadog_app_key
  datadog_gcp_integration_id    = module.firestore_us.datadog_gcp_integration_id
  datadog_gcp_integration_email = module.firestore_us.datadog_gcp_integration_email
}

provider "datadog" {
  api_key = local.datadog_api_key
  app_key = local.datadog_app_key
  api_url = "https://api.datadoghq.eu/"
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-dev"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-dev"
  }

  required_providers {
    datadog = {
      source  = "DataDog/datadog"
      version = "3.18.0"
    }
    google = {
      source  = "hashicorp/google"
      version = "4.44.1"
    }
  }

  required_version = "1.1.3"
}
