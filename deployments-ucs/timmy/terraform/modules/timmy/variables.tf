variable "branch_name" {
  description = "The branch name that the Timmy deployment uses"
  type        = string
}

variable "bucket_location" {
  description = "The location to use for the bucket to store the cloud function archives"
  type        = string
}

variable "project_id" {
  description = "The project ID where to deploy the resources"
  type        = string
}

variable "firestore_project_id" {
  description = "The project ID where to use firestore"
  type        = string
}

variable "region_prefix" {
  description = "The region prefix to name the resources"
  type        = string
}

variable "suffix_name" {
  description = "The suffix added at the end of the command, it is a hash for the branch name"
  type        = string
  default     = ""
}

variable "cloud_function_labels" {
  description = "The common labels to use for the cloud functions"
  type        = map(string)
  default     = {}
}

variable "region" {
  description = "The region to use for the resources"
  type        = string
}

variable "enable_request_portal_cloud_scheduler" {
  description = "Enable the cloud scheduler to trigger the timmy-request-portal cloud function"
  type        = bool
  default     = true
}

variable "cloud_scheduler_request_portal_schedule" {
  description = "The schedule to trigger the timmy-request-portal cloud function"
  type        = string
  default     = ""
}

variable "enable_request_portal_cloud_function" {
  description = "Enable the timmy-request-portal cloud function (ci usecase)"
  type        = bool
  default     = true
}

variable "cloud_scheduler_time_zone" {
  description = "The timezone to use for the cloud schedulers used by Timmy"
  type        = string
}

variable "enable_clean_firestore" {
  description = "Enable the timmy-clean-firestore cloud function and its cloud scheduler job"
  type        = bool
  default     = false
}

variable "cloud_scheduler_clean_firestore_schedule" {
  description = "The schedule to trigger the timmy-clean-firestore cloud function"
  type        = string
  default     = "0 * * * *"
}

variable "tenant_context_collection_name" {
  description = "The name of the tenant context collection in firestore"
  type        = string
  default     = "tenant_contexts"
}

variable "domain" {
  description = "The domain to use for the timmy-create-fire-doc"
  type        = string
}

variable "cloud_function_log_level" {
  description = "The log level to use for the cloud functions"
  type        = string
  default     = "info"
}

variable "portal_hostname" {
  description = "The portal hostname where to request tenants"
  type        = string
}

variable "portal_login_hostname" {
  description = "The portal login hostname where to authenticate"
  type        = string
}

variable "portal_tenant_continent" {
  description = "The continent to use to request tenants from the portal"
  type        = string
}

variable "portal_tenant_edition_flags" {
  description = "The editions flags to manage with Timmy"
  type        = list(string)
  default     = [
    "growth_edition_instance",
    "serenity_instance"
  ]
}

variable "portal_tenant_environment" {
  description = "The environment to use to request tenants from the portal"
  type        = string
}

variable "cloud_function_request_portal_available_memory" {
  description = "Available memory for the timmy request portal cloud function"
  type        = string
  default     = "256Mi"
}

variable "cloud_function_request_portal_timeout_seconds" {
  description = "The timeout to use for the request-portal cloud function"
  type        = number
  default     = 3600
}

variable "cloud_function_request_portal_max_instance_count" {
  description = "The max instance count for the request-portal cloud function"
  type        = number
  default     = 1
}

variable "cloud_function_create_tenant_available_memory" {
  description = "The available memory to affect to the create-tenant cloud function"
  type        = string
  default     = "256Mi"
}

variable "cloud_function_create_tenant_timeout_seconds" {
  description = "The timeout to use for the create-tenant cloud function"
  type        = number
  default     = 3600
}

variable "cloud_function_create_tenant_max_instance_count" {
  description = "The max instance count to use for the create-tenant"
  type        = number
  default     = 1000
}

variable "cloud_function_delete_tenant_available_memory" {
  description = "The available memory to affect to the delete-tenant cloud function"
  type        = string
  default     = "256Mi"
}

variable "cloud_function_delete_tenant_timeout_seconds" {
  description = "The timeout to use for the delete-tenant cloud function"
  type        = number
  default     = 3600
}

variable "cloud_function_delete_tenant_max_instance_count" {
  description = "The max instance count for the delete-tenant"
  type        = number
  default     = 1000
}

variable "cloud_function_create_doc_available_memory" {
  description = "The available memory to affect to the create-doc cloud function"
  type        = string
  default     = "128Mi"
}

variable "cloud_function_clean_firestore_available_memory" {
  description = "The available memory to affect to the clean-firestore cloud function"
  type        = string
  default     = "128Mi"
}

variable "cloud_function_delete_doc_available_memory" {
  description = "The available memory to affect to the delete-doc cloud function"
  type        = string
  default     = "128Mi"
}

variable "mailgun_domain" {
  description = "The mailgun domain to use for the tenant creation"
  type        = string
  default     = "mg.cloud.akeneo.com"
}

variable "mailgun_mailer_base_dsn" {
  description = "the mailer base dsn to use for the tenant creation"
  type        = string
  default     = "smtp://smtp.mailgun.org:2525"
}
