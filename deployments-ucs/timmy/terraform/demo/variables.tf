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
  description = "The region where to deploy Timmy"
  type        = string
}

variable "bucket_location" {
  description = "The location of the bucket used by Timmy"
  type        = string
}

variable "firestore_project_id" {
  description = "The project ID where the firestore is located"
  type        = string
}

variable "region_prefix" {
  description = "The region prefix to use for cloud function"
  type        = string
}

variable "domain" {
  description = "The domain to use for the tenant deployments"
  type        = string
  default     = "demo.pim.akeneo.cloud"
}

variable "function_labels" {
  description = "The labels to add to the cloud functions"
  type        = map(string)
  default     = {
    application = "timmy"
  }
}

variable "network_project_id" {
  description = "The Google project ID where the network is defined"
  type        = string
  default     = "akecld-prd-shared-infra"
}

variable "project_id" {
  description = "The Google project ID where to deploy the cloud functions"
  type        = string
  default     = "akecld-prd-pim-saas-demo"
}

variable "tenant_context_collection_name" {
  description = "The name of the tenant context collection in firestore"
  type        = string
  default     = "tenant_contexts"
}

variable "branch_name" {
  description = "The branch name that the Timmy deployment uses"
  type        = string
}

variable "suffix_name" {
  description = "The suffix added at the end of the command, it is a hash for the branch name"
  type        = string
  default     = ""
}

variable "portal_hostname" {
  description = "The portal hostname to use to request tenants"
  type        = string
  default     = "portal.akeneo.com"
}

variable "portal_login_hostname" {
  description = "The hostname to use to login to the portal"
  type        = string
  default     = "connect.akeneo.com"
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
  default     = "serenity_instance,growth_edition_instance"
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
