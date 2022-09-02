terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
  }
}

provider "google" {
  project = var.google_project_id
  version = ">= 3.90.1, < 4.0.0"
}

locals {
  type = var.types[var.product_reference_type]
  pfid = var.use_edition_flag  ? "srnt-${var.instance_name}" : "${local.type}-${var.instance_name}"
}
