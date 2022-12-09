variable "cloud_scheduler_request_portal_attempt_deadline" {
  description = "The deadline for job attempts. If the request handler does not respond by this deadline then the request is cancelled and the attempt is marked as a DEADLINE_EXCEEDED failure"
  type        = string
  default     = "30s"
}

module "cloud_scheduler_request_portal" {
  enable                     = var.enable_request_portal_cloud_scheduler
  source                     = "../cloud_scheduler"
  project_id                 = var.project_id
  region                     = var.region
  name                       = local.cloud_scheduler_request_portal_name
  description                = "Trigger ${module.cloud_function_request_portal.name} cloudfunction over HTTP"
  http_method                = local.cloud_scheduler_http_method
  http_target_uri            = module.cloud_function_request_portal.uri
  attempt_deadline           = var.cloud_scheduler_request_portal_attempt_deadline
  oidc_service_account_email = local.cloud_function_service_account_email
  oidc_token_audience        = module.cloud_function_request_portal.uri
  schedule                   = var.cloud_scheduler_request_portal_schedule
  time_zone                  = var.cloud_scheduler_time_zone
}

variable "cloud_scheduler_clean_firestore_attempt_deadline" {
  description = ""
  type        = string
  default     = "30s"
}

module "cloud_scheduler_clean_firestore" {
  enable                     = var.enable_clean_firestore
  source                     = "../cloud_scheduler"
  project_id                 = var.project_id
  region                     = var.region
  name                       = local.cloud_scheduler_clean_firestore_name
  description                = "Trigger ${module.cloud_function_clean_firestore.name} over HTTP"
  http_method                = local.cloud_scheduler_http_method
  http_target_uri            = module.cloud_function_clean_firestore.uri
  attempt_deadline           = var.cloud_scheduler_clean_firestore_attempt_deadline
  oidc_service_account_email = local.cloud_function_service_account_email
  oidc_token_audience        = module.cloud_function_clean_firestore.uri
  schedule                   = var.cloud_scheduler_clean_firestore_schedule
  time_zone                  = var.cloud_scheduler_time_zone
}
