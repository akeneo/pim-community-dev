variable "google_project_name" {
  type        = "string"
  description = "Ex. 'akecld-pim-ai' for prod and 'akeneo-dev' for other"
}

variable "cluster_dns_name" {
  type        = "string"
  default     = ""
  description = "Google cluster dns name provided by cloud team; used for dns CNAME to all pim-ai alias"
}

variable "pfid" {
  type        = "string"
  description = "retailer pfid"
}
variable "cluster_name" {
  type        = "string"
}

variable "subdomain" {
  type        = "string"
  default     = ""
  description = "sub-domain name in case the client doesn't want to use the pfid, it's the name that will prefix dns"
}

variable "MAILGUN_CLOUD_DOMAIN" {
  default = "mg.cloud.akeneo.com"
}

variable "MAILGUN_API_KEY" {
  default = "key-b66ddfb72ed72e53eb4371a1ffa7f4f8"
}

variable "MAILGUN_SMTP_PORT" {
  default = "2525"
}

variable "MAILGUN_SMTP_SERVER" {
  default = "smtp.mailgun.org"
}
