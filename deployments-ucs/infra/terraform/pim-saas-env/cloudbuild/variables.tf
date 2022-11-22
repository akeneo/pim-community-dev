variable "cloudbuild_project_id" {
  type    = string
  default = "akecld-prd-pim-saas-shared"
}

variable "cloudbuild_service_account" {
  type    = string
  default = "main-service-account"
}

variable "project_id" {
  type = string
}

variable "regions" {
  type = list(string)
}

variable "env" {
  type = string
}

variable "domain" {
  type    = string
  default = null
}

variable "domain_suffix" {
  type    = string
  default = "pim.akeneo.cloud"
}

variable "impersonate" {
  type    = string
  default = "main-service-account"
}

variable "tf_bucket" {
  type        = string
  description = "Name of terraform gcs state bucket"
}
