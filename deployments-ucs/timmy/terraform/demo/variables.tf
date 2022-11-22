variable "enable_timmy_request_portal" {
  description = "Deploy or not the timmy-request-portal function"
  type        = bool
  default     = true
}

variable "enable_timmy_cloudscheduler" {
  description = "Deploy or not the cloud-scheduler for timmy"
  type        = bool
  default     = true
}

variable "region" {
  type = string
}

variable "bucket_location" {
  type = string
}

variable "firestore_project_id" {
  type = string
}

variable "region_prefix" {
  type = string
}

variable "google_zone" {
  type = string
}

variable "domain" {
  type    = string
  default = "demo.pim.akeneo.com"
}

variable "function_labels" {
  type    = map(string)
  default = {
    application = "timmy"
  }
}

variable "network_project_id" {
  type    = string
  default = "akecld-prd-shared-infra"
}

variable "project_id" {
  type    = string
  default = "akecld-prd-pim-saas-demo"
}

variable "tenant_context_collection_name" {
  type    = string
  default = "tenant_contexts"
}

variable "branch_name" {
  type    = string
  default = "master"
}

variable "portal_hostname" {
  description = "The portal hostname to use to request tenants"
  type        = string
}

variable "portal_login_hostname" {
  description = "The hostname to use to login to the portal"
  type        = string
}

variable "portal_tenant_continent" {
  description = "The continent to use to filter tenants from the portal"
  type        = string
}

variable "portal_tenant_environment" {
  description = "The environment to use to filter tenants from the portal"
  type        = string
  default     = "sandbox"
}

variable "portal_tenant_edition_flags" {
  description = "The tenant edition flags to use to filter tenants from the portal (use comma to separe multiple edition flags)"
  type        = string
  default     = "serenity_instance"
}

variable "log_level" {
  description = "The cloud function log level to use"
  type        = string
  default     = "debug"
}

variable "time_zone" {
  description = "The time zone to use for the cloud-scheduler"
  type        = string
}

variable "schedule" {
  description = "The cron to use for the cloud-scheduler"
  type        = string
  default     = "*/2 * * * *"
}

variable "shared_project_id" {
  type    = string
}
