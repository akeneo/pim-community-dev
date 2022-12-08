locals {
  argocd_username                = "admin"
  argocd_password_secret_name    = "${upper(replace(var.region, "-", "_"))}_ARGOCD_PASSWORD"
  argocd_url                     = "https://argocd-${var.region}.${var.domain}"
  function_service_account_email = "timmy-cloud-function@${var.project_id}.iam.gserviceaccount.com"
  suffix                         = var.branch_name == "master" ? "" : "-${var.suffix_name}"
  suffix_name                    = local.suffix == "-" ? "" : "${local.suffix}"
  bucket_shorted                 = "bck"
  cloud_function_shorted         = "cfun"
  cloud_scheduler_shorted        = "csch"
  function_labels                = merge(var.function_labels, {
    application = "timmy"
    branch_name = var.branch_name
    environment = "dev"
    region      = var.region
  })
}

data "google_compute_zones" "google_compute_zones" {
  project = var.project_id
  region  = var.region
}

module "bucket" {
  source                      = "../modules/bucket"
  location                    = var.bucket_location
  name                        = substr("${var.region_prefix}-${local.bucket_shorted}-timmy${local.suffix_name}", 0, 63)
  project_id                  = var.project_id
  force_destroy               = true
  uniform_bucket_level_access = true
  versioning                  = true
}

module "timmy_request_portal" {
  source      = "../modules/cloudfunction"
  enable      = var.enable_timmy_request_portal
  project_id  = var.project_id
  name        = substr("${var.region_prefix}-${local.cloud_function_shorted}-timmy-request-portal${local.suffix_name}", 0, 63)
  description = "Request the portal to tenants to create/delete/update"
  labels      = merge(local.function_labels, {
    name = "timmy-request-portal"
  })
  available_memory    = "256Mi"
  bucket_name         = module.bucket.bucket_name
  entry_point         = "requestPortal"
  source_dir          = abspath("../../cloud-functions/portal")
  source_dir_excludes = [
    ".env",
    "README.md",
    "node_modules",
    "tests"
  ]
  location              = var.region
  service_account_email = local.function_service_account_email
  timeout_seconds       = 3600
  max_instance_count    = 1

  secret_environment_variables = [
    {
      key        = "TIMMY_PORTAL"
      project_id = var.project_id
      secret     = "TIMMY_PORTAL"
      version    = "latest"
    }
  ]

  environment_variables = {
    FUNCTION_URL_TIMMY_CREATE_TENANT = module.timmy_create_tenant.uri
    FUNCTION_URL_TIMMY_DELETE_TENANT = module.timmy_delete_tenant.uri
    GCP_PROJECT_ID                   = var.project_id
    HTTP_SCHEMA                      = "https"
    LOG_LEVEL                        = var.log_level
    NODE_ENV                         = "production"
    PORTAL_HOSTNAME                  = var.portal_hostname
    PORTAL_LOGIN_HOSTNAME            = var.portal_login_hostname
    TENANT_CONTINENT                 = var.portal_tenant_continent
    TENANT_EDITION_FLAGS             = var.portal_tenant_edition_flags
    TENANT_ENVIRONMENT               = var.portal_tenant_environment
  }
}

module "timmy_create_tenant" {
  source      = "../modules/cloudfunction"
  project_id  = var.project_id
  name        = substr("${var.region_prefix}-${local.cloud_function_shorted}-timmy-create-tenant${local.suffix_name}", 0, 63)
  description = "Create a new UCS tenant"
  labels      = merge(local.function_labels, {
    name = "timmy-create-tenant"
  })
  available_memory    = "256Mi"
  bucket_name         = module.bucket.bucket_name
  entry_point         = "createTenant"
  source_dir          = abspath("../../cloud-functions/tenants/create")
  source_dir_excludes = [
    ".env",
    "README.md",
    "node_modules",
    "tests"
  ]
  location              = var.region
  service_account_email = local.function_service_account_email
  timeout_seconds       = 3600
  max_instance_count    = 1000

  secret_environment_variables = [
    {
      key        = "ARGOCD_PASSWORD"
      project_id = var.project_id
      secret     = local.argocd_password_secret_name
      version    = "latest"
    },
    {
      key        = "MAILER_API_KEY"
      project_id = var.project_id
      secret     = "MAILER_API_KEY"
      version    = "latest"
    },
    // Presence of `TENANT_CONTEXT_ENCRYPTION_KEY` enables the encryption in Firestore
    {
      key        = "TENANT_CONTEXT_ENCRYPTION_KEY"
      project_id = var.project_id
      secret     = "TENANT_CONTEXT_ENCRYPTION_KEY"
      version    = "latest"
    }
  ]

  environment_variables = {
    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = local.argocd_username
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    GOOGLE_ZONES                   = join(",", data.google_compute_zones.google_compute_zones.names)
    LOG_LEVEL                      = var.log_level
    MAILER_DOMAIN                  = "mg.cloud.akeneo.com"
    NODE_ENV                       = "production"
    REGION                         = var.region
    SOURCE_PATH                    = "tenant"
    SOURCE_REPO_URL                = "https://github.com/akeneo/pim-saas-k8s-artifacts.git"
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
}

module "timmy_delete_tenant" {
  source      = "../modules/cloudfunction"
  project_id  = var.project_id
  name        = substr("${var.region_prefix}-${local.cloud_function_shorted}-timmy-delete-tenant${local.suffix_name}", 0, 63)
  description = "Delete an UCS tenant"
  labels      = merge(local.function_labels, {
    name = "timmy-delete-tenant"
  })
  available_memory      = "256Mi"
  bucket_name           = module.bucket.bucket_name
  entry_point           = "deleteTenant"
  source_dir            = abspath("../../cloud-functions/tenants/delete")
  location              = var.region
  service_account_email = local.function_service_account_email
  timeout_seconds       = 3600
  max_instance_count    = 1000

  secret_environment_variables = [
    {
      key        = "ARGOCD_PASSWORD"
      project_id = var.project_id
      secret     = local.argocd_password_secret_name
      version    = "latest"
    },
  ]

  environment_variables = {

    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = local.argocd_username
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    LOG_LEVEL                      = var.log_level
    NODE_ENV                       = "production"
    REGION                         = var.region
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
}

module "timmy_clean_firestore" {
  source              = "../modules/cloudfunction"
  project_id          = var.project_id
  name                = substr("${var.region_prefix}-${local.cloud_function_shorted}-timmy-clean-firestore${local.suffix_name}", 0, 63)
  description         = "Clean Firestore documents of not existing tenants in ArgoCD server"
  bucket_name         = module.bucket.bucket_name
  entry_point         = "cleanFirestore"
  source_dir          = abspath("../../cloud-functions/clean/firestore")
  source_dir_excludes = [
    "node_modules",
  ]
  location              = var.region
  service_account_email = local.function_service_account_email

  secret_environment_variables = [
    {
      key        = "ARGOCD_PASSWORD"
      project_id = var.project_id
      secret     = local.argocd_password_secret_name
      version    = "latest"
    }
  ]

  environment_variables = {
    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = local.argocd_username
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    LOG_LEVEL                      = var.log_level
    REGION                         = var.region
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
}

module "timmy_create_fire_document" {
  source      = "../modules/cloudfunction"
  project_id  = var.project_id
  name        = substr("${var.region_prefix}-${local.cloud_function_shorted}-timmy-create-doc${local.suffix_name}", 0, 63)
  description = "Create Firestore document in the tenant context DB"
  labels      = merge(local.function_labels, {
    name = "timmy-create-doc"
  })
  available_memory      = "128Mi"
  bucket_name           = module.bucket.bucket_name
  entry_point           = "createDocument"
  source_dir            = abspath("../../cloud-functions/timmy-firestore/create")
  location              = var.region
  service_account_email = local.function_service_account_email

  secret_environment_variables = [
    {
      key        = "TENANT_CONTEXT_ENCRYPTION_KEY"
      project_id = var.project_id
      secret     = "TENANT_CONTEXT_ENCRYPTION_KEY"
      version    = "latest"
    }
  ]
  environment_variables = {
    domain             = var.domain
    projectId          = var.project_id
    fireStoreProjectId = var.firestore_project_id
    mailerBaseDsn      = "smtp://smtp.mailgun.org:2525"
    tenantContext      = var.tenant_context_collection_name
  }
}

module "timmy_delete_fire_document" {
  source      = "../modules/cloudfunction"
  project_id  = var.project_id
  name        = substr("${var.region_prefix}-${local.cloud_function_shorted}-timmy-delete-doc${local.suffix_name}", 0, 63)
  description = "Delete Firestore document in the tenant context DB"
  labels      = merge(local.function_labels, {
    name = "timmy-delete-doc"
  })
  available_memory      = "128Mi"
  bucket_name           = module.bucket.bucket_name
  entry_point           = "deleteDocument"
  source_dir            = abspath("../../cloud-functions/timmy-firestore/delete")
  location              = var.region
  service_account_email = local.function_service_account_email

  environment_variables = {
    projectId          = var.project_id
    fireStoreProjectId = var.firestore_project_id
    tenantContext      = var.tenant_context_collection_name
  }
}

module "timmy_cloudscheduler" {
  enable                     = var.enable_timmy_cloudscheduler
  source                     = "../modules/cloudscheduler"
  project_id                 = var.project_id
  region                     = var.region
  name                       = substr("${var.region_prefix}-${local.cloud_scheduler_shorted}-timmy-request-portal${local.suffix_name}", 0, 63)
  description                = "Trigger timmy-request-portal cloudfunction every 2 minutes"
  http_method                = "POST"
  http_target_uri            = module.timmy_request_portal.uri
  attempt_deadline           = "30s"
  oidc_service_account_email = local.function_service_account_email
  oidc_token_audience        = module.timmy_request_portal.uri
  schedule                   = var.schedule
  time_zone                  = var.time_zone
}

module "timmy_cloudscheduler_clean_firestore" {
  enable                     = var.enable_timmy_cloudscheduler
  source                     = "../modules/cloudscheduler"
  project_id                 = var.project_id
  region                     = var.region
  name                       = substr("${var.region_prefix}-${local.cloud_scheduler_shorted}-timmy-clean-firestore${local.suffix_name}", 0, 63)
  description                = "Trigger timmy-clean-firestore every 1 hour"
  http_method                = "POST"
  http_target_uri            = module.timmy_clean_firestore.uri
  attempt_deadline           = "30s"
  oidc_service_account_email = local.function_service_account_email
  oidc_token_audience        = module.timmy_clean_firestore.uri
  schedule                   = "0 * * * *"
  time_zone                  = var.time_zone
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-dev"
  }

  required_providers {
    archive = {
      source  = "hashicorp/archive"
      version = "2.2.0"
    }
    google = {
      source  = "hashicorp/google"
      version = "4.44.1"
    }
  }

  required_version = "1.1.3"
}
