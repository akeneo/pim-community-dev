terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
  }
}

provider "google" {
  project = var.google_project_id
  version = ">= 3.90.1, < 4.0.0"
}

provider "helm" {
  version = ">= 0.10.5, < 1.0.0"
  kubernetes {
    config_path = ".kubeconfig"
  }
}

terraform {
  required_providers {
    mailgun = {
      source = "wgebis/mailgun"
      version = "0.7.1"
    }
  }
}

provider "mailgun" {
  api_key = var.mailgun_api_key
}

locals {
  type                            = var.types[var.product_reference_type]
  pfid                            = var.use_edition_flag ? "srnt-${var.instance_name}" : "${local.type}-${var.instance_name}"
  monitoring_authentication_token = var.monitoring_authentication_token != "" ? var.monitoring_authentication_token : random_string.monitoring_authentication_token.result
}

data "google_dns_managed_zone" "main" {
  name    = var.dns_zone
  project = var.dns_project
}

resource "google_dns_record_set" "main" {
  name         = var.dns_external
  type         = "CNAME"
  ttl          = 300
  managed_zone = var.dns_zone
  rrdatas      = [var.dns_internal]
  project      = var.dns_project
}
