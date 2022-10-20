locals {
  argocd_url                           = "https://argocd-${var.region}.${var.domain}"
  cloudscheduler_service_account_email = "timmy-deployment@${var.project_id}.iam.gserviceaccount.com"
  function_service_account_email       = "timmy-cloud-function@${var.project_id}.iam.gserviceaccount.com"
  portal_hostname                      = "wiremock-${var.region}.${var.domain}"
}

module "bucket" {
  source                      = "../modules/bucket"
  location                    = var.bucket_location
  name                        = "${var.project_id}-timmy"
  project_id                  = var.project_id
  force_destroy               = true
  uniform_bucket_level_access = true
  versioning                  = true
}

module "timmy_request_portal" {
  source              = "../modules/cloudfunction"
  project_id          = var.project_id
  name                = "${var.region_prefix}-timmy-request-portal"
  description         = "Request the portal to tenants to create/delete/update"
  available_memory    = "128Mi"
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
    // TODO: switch to https when PH-202 is released
    HTTP_SCHEMA                      = "http"
    LOG_LEVEL                        = "info"
    NODE_ENV                         = "production"
    // TODO: replace portal hostnames with the private entry once PH-202 is released
    PORTAL_HOSTNAME                  = local.portal_hostname
    PORTAL_LOGIN_HOSTNAME            = local.portal_hostname
    TENANT_CONTINENT                 = "europe"
    TENANT_EDITION_FLAGS             = "serenity_instance"
    TENANT_ENVIRONMENT               = "sandbox"
  }
}

module "timmy_create_tenant" {
  source              = "../modules/cloudfunction"
  project_id          = var.project_id
  name                = "${var.region_prefix}-timmy-create-tenant"
  description         = "Create a new UCS tenant"
  available_memory    = "128Mi"
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
      secret     = "ARGOCD_PASSWORD"
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
    ARGOCD_USERNAME                = "admin"
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    GOOGLE_ZONE                    = var.google_zone
    LOG_LEVEL                      = "debug"
    MAILER_BASE_URL                = "smtp://smtp.mailgun.org:2525"
    MAILER_DOMAIN                  = "mg.cloud.akeneo.com"
    NODE_ENV                       = "production"
    PIM_IMAGE_REPOSITORY           = "europe-west1-docker.pkg.dev/akecld-prd-pim-saas-shared/prod/pim-enterprise-dev"
    PIM_IMAGE_TAG                  = "v20220920013749"
    REGION                         = var.region
    SOURCE_PATH                    = "tenant"
    SOURCE_REPO_URL                = "https://github.com/akeneo/pim-saas-k8s-artifacts.git"
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
  
}

module "timmy_delete_tenant" {
  source                = "../modules/cloudfunction"
  project_id            = var.project_id
  name                  = "${var.region_prefix}-timmy-delete-tenant"
  description           = "Delete an UCS tenant"
  available_memory      = "128Mi"
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
      secret     = "ARGOCD_PASSWORD"
      version    = "latest"
    },
  ]
  
  environment_variables = {

    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = "admin"
    GCP_FIRESTORE_PROJECT_ID       = "akecld-prd-pim-fire-eur-dev"
    GCP_PROJECT_ID                 = var.project_id
    NODE_ENV                       = "production"
    REGION                         = var.region
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
}

module "timmy_create_fire_document" {
  source                = "../modules/cloudfunction"
  project_id            = var.project_id
  name                  = "${var.region_prefix}-timmy-create-doc"
  description           = "Create Firestore document in the tenantcontext DB"
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
    mailerBaseUrl      = "smtp://smtp.mailgun.org:2525"
    tenantContext      = var.tenant_context_collection_name
  }
}

module "timmy_delete_fire_document" {
  source                = "../modules/cloudfunction"
  project_id            = var.project_id
  name                  = "${var.region_prefix}-timmy-delete-doc"
  description           = "Delete Firestore document in the tenantcontext DB"
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
  source                     = "../modules/cloudscheduler"
  project_id                 = var.project_id
  region                     = var.region
  name                       = "${var.region_prefix}-timmy-request-portal"
  description                = "Trigger timmy-request-portal cloudfunction every 2 minutes"
  http_method                = "POST"
  http_target_uri            = module.timmy_request_portal.uri
  attempt_deadline           = "30s"
  oidc_service_account_email = local.function_service_account_email
  oidc_token_audience        = module.timmy_request_portal.uri
  schedule                   = "*/2 * * * *"
  time_zone                  = "Europe/Paris"
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-dev"
  }
  
  required_providers {
    archive = {
      source  = "hashicorp/archive"
      version = "= 2.2.0"
    }
    google = {
      source  = "hashicorp/google"
      version = "= 4.35.0"
    }
  }
}
