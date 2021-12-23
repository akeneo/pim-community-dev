variable "google_project_id" {
  type = string
}

variable "prefix_resources" {
  type    = string
  default = "trial-redirect"
}

variable "redirection_url" {
  type    = string
  default = "trial.akeneo.com"
}

variable "source_domain" {
  type    = string
  default = "trial.akeneo.cloud"
}

variable "google_project_secret" {
  type    = string
  default = "akeneo-cloud"
}

variable "dns_zone" {
  type    = string
  default = "trial-akeneo-cloud"
}

variable "dns_project" {
  type    = string
  default = "akecld-saas-trial"
}
