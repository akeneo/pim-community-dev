variable "project_id" {
  type = string
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
