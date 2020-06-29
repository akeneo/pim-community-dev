module "pim-monitoring" {
  source = "${PIM_SRC_DIR}/deployments/terraform/monitoring"

  google_project_id      = module.pim.google_project_id
  instance_name          = module.pim.instance_name
  dns_external           = module.pim.dns_external
  helm_exec_id           = module.pim.helm_exec_id
  pager_duty_service_key = "d55f85282a8e4e16b2c822249ad440bd"
}

