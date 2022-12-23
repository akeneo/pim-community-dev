locals {
  datadog_api_key = data.google_secret_manager_secret_version.datadog_api_key.secret_data
  datadog_app_key = data.google_secret_manager_secret_version.datadog_app_key.secret_data
}

data "google_secret_manager_secret_version" "datadog_api_key" {
  secret  = "DATADOG_API_KEY"
  project = var.shared_project_id
}

data "google_secret_manager_secret_version" "datadog_app_key" {
  secret  = "DATADOG_APP_KEY"
  project = var.shared_project_id
}

module "iam" {
  source                = "../../modules/iam"
  project_id            = var.project_id
  source_project_id     = var.source_project_id
  firestore_projects_id = [for loc, proj in var.firestore_locations : proj]
  secrets_admins        = var.admins
  cloudbuild_admins     = var.admins
}

module "firestore" {
  source        = "../../modules/firestore"
  for_each      = var.firestore_locations
  location_id   = each.key
  project_id    = each.value
  database_type = "CLOUD_FIRESTORE"
}

module "secrets" {
  source     = "../../modules/secrets"
  project_id = var.project_id
  secrets = concat([
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
        "serviceAccount:${module.iam.pim_sa_email}",
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "tenant-context-encryption-key"
      }
    }
    ], flatten([
      for region in var.regions : {

        name = "${upper(replace(region, "-", "_"))}_ARGOCD_PASSWORD"
        members = [
          "serviceAccount:${module.iam.portal_function_sa_email}"
        ],

        labels = {
          usage  = "argocd"
          region = region
        }
      }
  ]))
}

module "tenant_context_encryption_key" {
  source    = "../../modules/tenant-context-secret"
  secret_id = module.secrets.google_secrets_ids["TENANT_CONTEXT_ENCRYPTION_KEY"]
}

module "public_dns" {
  source     = "../../modules/public-dns"
  project_id = var.project_id
  zone_name  = var.public_zone

  forward = {
    target_project_id = var.shared_project_id
    target_zone_name  = var.shared_zone_name
  }
}

module "private_dns" {
  source     = "../../modules/private-dns"
  project_id = var.project_id
  zone_name  = var.private_zone
  networks   = []
  #networks   = [data.google_compute_network.vpc.id]
}

module "cloudarmor" {
  source     = "../../modules/cloudarmor"
  project_id = var.project_id

  enable_rate_limiting_api = false
}

module "timmy_datadog" {
  source                        = "../../modules/datadog"
  project_id                    = var.project_id
  datadog_api_key               = local.datadog_api_key
  datadog_app_key               = local.datadog_app_key
  datadog_gcp_integration_id    = module.iam.datadog_gcp_integration_id
  datadog_gcp_integration_email = module.iam.datadog_gcp_integration_email
}

module "google_workflows" {
  count      = var.enable_migrations_workflow ? 1 : 0
  source     = "../../modules/workflows"
  project_id = var.project_id
  region     = element(var.regions, 0)
}

provider "datadog" {
  api_key = local.datadog_api_key
  app_key = local.datadog_app_key
  api_url = "https://api.datadoghq.eu/"
}

terraform {
  backend "gcs" {}

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
