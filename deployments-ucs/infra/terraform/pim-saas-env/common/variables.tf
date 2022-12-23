variable "project_id" {
  type = string
}
variable "project_name" {
  type = string
}
variable "source_project_id" {
  description = "Project wich hosts the legacy tenants. Used for migration from legacy to UCS."
  type        = list(string)
  default     = []
}
variable "regions" {
  type = list(string)
}

variable "firestore_locations" {
  type = map(string)
}

variable "shared_project_id" {
  type    = string
  default = "akecld-prd-pim-saas-shared"
}

variable "admins" {
  type = list(string)
}

variable "public_zone" {
  type = string
}

variable "private_zone" {
  type = string
}

variable "shared_zone_name" {
  type = string
}

variable "enable_migrations_workflow" {
  type    = bool
  default = false
}
