terraform {
  backend "gcs" {
    bucket  = "akecld-terraform"
    prefix  = "saas/akecld-saas-dev/europe-west3-a/srnt-tip-1315/"
    project = "akeneo-cloud"
  }
}

module "pim" {
  #source = "git@github.com:akeneo/pim-enterprise-cloud.git//infra/terraform?ref=v3.2.5-01"
  source = "/home/franck/code/pim-enterprise-dev/infra/terraform"

  instance_name           = "tip-1315"
  google_project_zone     = "europe-west3-a"
  google_project_id       = "akecld-saas-dev"
  dns_external            = "tip-1315.dev.cloud.akeneo.com."
  dns_internal            = "europe-west3-a-akecld-saas-dev.dev.cloud.akeneo.com."
  dns_zone                = "dev-cloud-akeneo-com"
  pager_duty_service_key  = "d55f85282a8e4e16b2c822249ad440bd"
  google_storage_location = "eu"
  papo_project_code       = "NOT_ON_PAPO"
}
