variable "project_id" {
  type = string
}

variable "region" {
  type = string
}

variable "name" {
  type    = string
  default = null
}

variable "mysql_zones" {
  type    = list(string)
  default = ["b"]
}

variable "gke_sa_email" {
  type    = string
  default = null
}

variable "host_project_id" {
  type = string
}

variable "shared_vpc_name" {
  type = string
}

variable "viewers" {
  type = list(string)
}

variable "admins" {
  type = list(string)
}

variable "min_master_version" {
  type    = string
  default = "1.24"
}

variable "default_machine type" {
  type    = string
  default = "n1-standard-4"
}

variable "mysql_machine type" {
  type    = string
  default = "n1-highmem-4"
}

variable "es_machine type" {
  type    = string
  default = "n1-highmem-4"
}
