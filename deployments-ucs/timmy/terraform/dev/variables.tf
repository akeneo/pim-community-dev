variable "branch_name" {
  description = "The branch name that the Timmy deployment uses"
  type        = string
}

variable "bucket_location" {
  description = "The location used for the bucket to store cloud function archives"
  type        = string
}

variable "cloud_scheduler_time_zone" {
  description = "The time zone to use for the cloud scheduler job(s)"
  type        = string
}

variable "project_id" {
  description = "The Google project ID where to deploy Timmy"
  type        = string
  default     = "akecld-prd-pim-saas-dev"
}

variable "region" {
  description = "The region where to deploy the resources"
  type        = string
}

variable "region_prefix" {
  description = "The region prefix used to name the resources"
  type        = string
}

variable "domain" {
  description = "The domain to use for the dev environment"
  type        = string
  default     = "ci.pim.akeneo.cloud"
}

variable "firestore_project_id" {
  description = "The project ID where to use the firestore"
  type        = string
}

variable "portal_hostname" {
  description = "The portal hostname where to request the tenants to manage"
  type        = string
  default     = "portal-dev3-sandbox.ip.akeneo.com"
}

variable "portal_login_hostname" {
  description = "The portal login hostname to use to authenticate"
  type        = string
  default     = "connect-sandbox.ip.akeneo.com"
}

variable "portal_tenant_continent" {
  description = "The continent to use to request the tenants"
  type        = string
}

variable "portal_tenant_environment" {
  description = "The environment to use to request the tenants"
  type        = string
  default     = "sandbox"
}

variable "enable_clean_firestore" {
  description = "Enable the timmy-clean-firestore cloud function and the cloud scheduler"
  type        = bool
  default     = true
}

variable "enable_request_portal_cloud_function" {
  description = "Enable the timmy-request-portal cloud function (used by ci usecase with false)"
  type        = bool
  default     = true
}

variable "enable_request_portal_cloud_scheduler" {
  description = "Enable the cloud scheduler to trigger timmy-request-portal (used by ci usecase with false)"
  type        = bool
  default     = true
}

variable "suffix_name" {
  description = "The suffix added at the end of the command, it is a hash for the branch name"
  type        = string
  default     = ""
}

variable "cloud_scheduler_request_portal_schedule" {
  description = "Schedule to trigger the timmy-request-portal cloud function"
  type        = string
  default     = "*/2 * * * *"
}

variable "cloud_scheduler_request_portal_attempt_deadline" {
  description = "The deadline for job attempts. If the request handler does not respond by this deadline then the request is cancelled and the attempt is marked as a DEADLINE_EXCEEDED failure"
  type        = string
  default     = "30s"
}

variable "mysql_disk_size" {
  description = "The disk size for the MySQL tenant component"
  type        = number
  default     = 10
}
