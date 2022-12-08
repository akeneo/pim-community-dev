terraform {
  backend "gcs" {
    bucket = "akecld-terraform-pim-saas-sandbox"
    prefix = "infra/pim-saas/akecld-prd-pim-saas-sandbox"
  }

  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "4.44.1"
    }
  }

  required_version = "1.1.3"
}
