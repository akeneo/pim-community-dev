variable "bucket_name" {
  default = ""
}

variable "dns_external" {
  type = string
}

variable "dns_internal" {
  type = string
}

variable "dns_project" {
  type    = string
  default = "akeneo-cloud"
}

variable "dns_zone" {
  type = string
}

variable "force_destroy_storage" {
  default = "false"
}

variable "google_project_id" {
  type = string
}

variable "google_project_zone" {
  type        = string
  description = "Google zone where the project is deployed (Used for backup)."
}

variable "google_storage_location" {
  type = string
}

variable "instance_name" {
  type = string
}

variable "mailgun_api_key" {
  type    = string
  default = "key-b66ddfb72ed72e53eb4371a1ffa7f4f8"
}

variable "mailgun_domain" {
  type    = string
  default = "mg.cloud.akeneo.com"
}

variable "mailgun_host" {
  default = "smtp.mailgun.org"
}

variable "mailgun_port" {
  default = "2525"
}

variable "monitoring_authentication_token" {
  type    = string
  default = ""
}

variable "papo_project_code" {
  description = "Code of the project on Akeneo portal"
}

variable "pim_version" {
  default = ""
}

variable "product_reference_code" {
  type = string
}

variable "product_reference_type" {
  type = string
}

variable "type" {
  type = string
  default = "srnt"
}

variable "mysql_disk_name" {
  type    = string
  default = ""
}

variable "mysql_disk_description" {
  type    = string
  default = ""
}

variable "mysql_disk_size" {
  type        = number
  description = "size in GB"
  default     = 100
}

variable "mysql_source_snapshot" {
  default = ""
  type    = string
}

variable "mysql_disk_type" {
  default = "pd-ssd"
  type    = string
}
