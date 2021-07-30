terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
  }
}

provider "google" {
  project = var.google_project_id
  version = ">= 3.21.0, < 4.0.0"
}

locals {
  type = "${var.types[var.product_reference_type]}"
  pfid = "${local.type}-${var.instance_name}"
}
