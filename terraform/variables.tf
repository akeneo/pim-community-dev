variable "dns_external" {
  type = "string"
}

variable "dns_internal" {
  type = "string"
}

variable "dns_zone" {
  type    = "string"
  default = "cloud-akeneo-com"
}

variable "dns_project" {
  type    = "string"
  default = "akeneo-cloud"
}

variable "mailgun_login" {
  type = "string"
}

variable "mailgun_password" {
  type = "string"
}

variable "mailgun_api_key" {
  type    = "string"
  default = "key-b66ddfb72ed72e53eb4371a1ffa7f4f8"
}

variable "mailgun_domain" {
  type    = "string"
  default = "mg.cloud.akeneo.com"
}
