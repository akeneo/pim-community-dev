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
  default = "ci.pim.akeneo.cloud"
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
  default = "akecld-prd-pim-saas-dev"
}

variable "tenant_context_collection_name" {
  type    = string
  default = "tenant_contexts"
}

variable "branch_name" {
  type    = string
}
