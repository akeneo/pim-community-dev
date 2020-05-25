terraform {
  required_version = "= 0.12.23"
}

provider "google" {
  version = "~> 3.17.0"
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform-dev"
    prefix = "saas/${GOOGLE_PROJECT_ID}/${GOOGLE_CLUSTER_ZONE}/${PFID}"
  }
}

module "pim" {
  source = "${PIM_SRC_DIR}/deployments/terraform"

  google_project_id       = "${GOOGLE_PROJECT_ID}"
  google_project_zone     = "${GOOGLE_CLUSTER_ZONE}"
  instance_name           = "${INSTANCE_NAME}"
  dns_external            = "${INSTANCE_NAME}.${GOOGLE_MANAGED_ZONE_DNS}."
  dns_internal            = "${CLUSTER_DNS_NAME}"
  dns_zone                = "${GOOGLE_MANAGED_ZONE_NAME}"
  google_storage_location = "EU"
  papo_project_code       = "NOT_ON_PAPO_${PFID}"
  force_destroy_storage   = true
  pim_version             = "${IMAGE_TAG}"
}

module "pim-monitoring" {
  source = "${PIM_SRC_DIR}/deployments/terraform/monitoring"

  google_project_id      = module.pim.google_project_id
  instance_name          = module.pim.instance_name
  dns_external           = module.pim.dns_external
  helm_exec_id           = module.pim.helm_exec_id
  pager_duty_service_key = "d55f85282a8e4e16b2c822249ad440bd"
}
