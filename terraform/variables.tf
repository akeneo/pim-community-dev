variable "google_project_name" {
  type = "string"
}

variable "google_project_zone" {
  type        = "string"
  description = "Google zone where the project is deployed (Used for backup)."
}

variable "pager_duty_service_key" {
  type        = "string"
  description = "PagerDuty Service key, get it on the pagerduty page of the service"
}

variable "pfid" {
  type = "string"
}

variable "dns_external" {
  type = "string"
}

variable "dns_internal" {
  type = "string"
}

variable "dns_zone" {
  type = "string"
}

variable "dns_project" {
  type    = "string"
  default = "akeneo-cloud"
}

variable "mailgun_api_key" {
  type    = "string"
  default = "key-b66ddfb72ed72e53eb4371a1ffa7f4f8"
}

variable "mailgun_domain" {
  type    = "string"
  default = "mg.cloud.akeneo.com"
}

variable "mailgun_host" {
  default = "smtp.mailgun.org"
}

variable "mailgun_port" {
  default = "2525"
}
