module "cloud_function_request_portal" {
  source                = "../cloud_function"
  available_memory      = var.cloud_function_request_portal_available_memory
  bucket_name           = module.bucket.bucket_name
  description           = "Request the portal to tenants to create/delete UCS tenant"
  enable                = var.enable_request_portal_cloud_function
  entry_point           = "requestPortal"
  environment_variables = {
    FUNCTION_URL_TIMMY_CREATE_TENANT = module.cloud_function_create_tenant.uri
    FUNCTION_URL_TIMMY_DELETE_TENANT = module.cloud_function_delete_tenant.uri
    GCP_PROJECT_ID                   = var.project_id
    HTTP_SCHEMA                      = "https"
    LOG_LEVEL                        = var.cloud_function_log_level
    NODE_ENV                         = "production"
    PORTAL_HOSTNAME                  = var.portal_hostname
    PORTAL_LOGIN_HOSTNAME            = var.portal_login_hostname
    TENANT_CONTINENT                 = var.portal_tenant_continent
    TENANT_EDITION_FLAGS             = join(",", var.portal_tenant_edition_flags)
    TENANT_ENVIRONMENT               = var.portal_tenant_environment
  }
  labels                       = local.cloud_function_labels
  location                     = var.region
  max_instance_count           = var.cloud_function_request_portal_max_instance_count
  name                         = local.cloud_function_request_portal_name
  project_id                   = var.project_id
  secret_environment_variables = [
    {
      key        = "TIMMY_PORTAL"
      project_id = var.project_id
      secret     = "TIMMY_PORTAL"
      version    = "latest"
    }
  ]
  service_account_email = local.cloud_function_service_account_email
  source_dir            = abspath("../../cloud-functions/portal")
  source_dir_excludes   = [
    ".env",
    "README.md",
    "node_modules",
    "tests"
  ]
  timeout_seconds = var.cloud_function_request_portal_timeout_seconds
}

module "cloud_function_create_tenant" {
  source                = "../cloud_function"
  available_memory      = var.cloud_function_create_tenant_available_memory
  bucket_name           = module.bucket.bucket_name
  description           = "Create a new UCS tenant"
  entry_point           = "createTenant"
  environment_variables = {
    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = local.argocd_username
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    GOOGLE_ZONES                   = join(",", data.google_compute_zones.google_compute_zones.names)
    LOG_LEVEL                      = var.cloud_function_log_level
    MAILER_DOMAIN                  = var.mailgun_domain
    NODE_ENV                       = "production"
    REGION                         = var.region
    SOURCE_PATH                    = "tenant"
    SOURCE_REPO_URL                = "https://github.com/akeneo/pim-saas-k8s-artifacts.git"
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
  labels                       = local.cloud_function_labels
  location                     = var.region
  max_instance_count           = var.cloud_function_create_tenant_max_instance_count
  name                         = local.cloud_function_create_tenant_name
  project_id                   = var.project_id
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
  service_account_email = local.cloud_function_service_account_email
  source_dir            = abspath("../../cloud-functions/tenants/create")
  source_dir_excludes   = [
    ".env",
    "README.md",
    "node_modules",
    "tests"
  ]
  timeout_seconds = var.cloud_function_create_tenant_timeout_seconds
}

module "cloud_function_delete_tenant" {
  source                = "../cloud_function"
  available_memory      = var.cloud_function_delete_tenant_available_memory
  bucket_name           = module.bucket.bucket_name
  description           = "Delete an UCS tenant"
  entry_point           = "deleteTenant"
  environment_variables = {
    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = local.argocd_username
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    LOG_LEVEL                      = var.cloud_function_log_level
    NODE_ENV                       = local.cloud_function_node_env
    REGION                         = var.region
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
  labels                       = local.cloud_function_labels
  location                     = var.region
  max_instance_count           = var.cloud_function_delete_tenant_max_instance_count
  name                         = local.cloud_function_delete_tenant_name
  project_id                   = var.project_id
  secret_environment_variables = [
    {
      key        = "ARGOCD_PASSWORD"
      project_id = var.project_id
      secret     = local.argocd_password_secret_name
      version    = "latest"
    },
  ]
  service_account_email = local.cloud_function_service_account_email
  source_dir            = abspath("../../cloud-functions/tenants/delete")
  source_dir_excludes   = [
    ".env",
    "README.md",
    "node_modules",
    "tests"
  ]
  timeout_seconds = var.cloud_function_delete_tenant_timeout_seconds
}

module "cloud_function_clean_firestore" {
  source                = "../cloud_function"
  available_memory      = var.cloud_function_clean_firestore_available_memory
  bucket_name           = module.bucket.bucket_name
  description           = "Clean Firestore documents of not existing tenants in ArgoCD server"
  enable                = var.enable_clean_firestore
  entry_point           = "cleanFirestore"
  environment_variables = {
    ARGOCD_URL                     = local.argocd_url
    ARGOCD_USERNAME                = local.argocd_username
    GCP_FIRESTORE_PROJECT_ID       = var.firestore_project_id
    GCP_PROJECT_ID                 = var.project_id
    LOG_LEVEL                      = var.cloud_function_log_level
    REGION                         = var.region
    TENANT_CONTEXT_COLLECTION_NAME = var.tenant_context_collection_name
  }
  location                     = var.region
  name                         = local.cloud_function_clean_firestore_name
  project_id                   = var.project_id
  secret_environment_variables = [
    {
      key        = "ARGOCD_PASSWORD"
      project_id = var.project_id
      secret     = local.argocd_password_secret_name
      version    = "latest"
    }
  ]
  service_account_email = local.cloud_function_service_account_email
  source_dir            = abspath("../../cloud-functions/clean/firestore")
  source_dir_excludes   = [
    "node_modules"
  ]
}

module "cloud_function_create_doc" {
  source                = "../cloud_function"
  available_memory      = var.cloud_function_create_doc_available_memory
  bucket_name           = module.bucket.bucket_name
  description           = "Create a Firestore document in the tenant context DB"
  entry_point           = "createDocument"
  environment_variables = {
    domain             = var.domain
    projectId          = var.project_id
    fireStoreProjectId = var.firestore_project_id
    mailerBaseDsn      = var.mailgun_mailer_base_dsn
    tenantContext      = var.tenant_context_collection_name
  }
  labels                = local.cloud_function_labels
  location              = var.region
  name                  = local.cloud_function_create_doc_name
  project_id            = var.project_id
  service_account_email = local.cloud_function_service_account_email
  source_dir            = abspath("../../cloud-functions/timmy-firestore/create")
  source_dir_excludes   = [
    "node_modules"
  ]
  secret_environment_variables = [
    {
      key        = "TENANT_CONTEXT_ENCRYPTION_KEY"
      project_id = var.project_id
      secret     = "TENANT_CONTEXT_ENCRYPTION_KEY"
      version    = "latest"
    }
  ]
}

module "cloud_function_delete_doc" {
  source                = "../cloud_function"
  available_memory      = var.cloud_function_delete_doc_available_memory
  bucket_name           = module.bucket.bucket_name
  description           = "Delete a Firestore document in the tenant context DB"
  entry_point           = "deleteDocument"
  environment_variables = {
    projectId          = var.project_id
    fireStoreProjectId = var.firestore_project_id
    tenantContext      = var.tenant_context_collection_name
  }
  labels                = local.cloud_function_labels
  location              = var.region
  name                  = local.cloud_function_delete_doc_name
  project_id            = var.project_id
  service_account_email = local.cloud_function_service_account_email
  source_dir            = abspath("../../cloud-functions/timmy-firestore/delete")
  source_dir_excludes   = [
    "node_modules"
  ]
}
