data "google_secret_manager_secret_version" "bigcommerce_connector_akeneo_connect_bot_password" {
  count = contains(local.bc_enabled_projects, var.google_project_id) ? 1 : 0
  secret  = var.google_project_id != "akecld-saas-dev" ? "connector-bigcommerce-prod-portal-client-password" : "connector-bigcommerce-dev-portal-client-password"
  project = "akeneo-cloud"
}

data "google_secret_manager_secret_version" "bigcommerce_connector_akeneo_connect_bot_client_secret" {
  count = contains(local.bc_enabled_projects, var.google_project_id) ? 1 : 0
  secret  = var.google_project_id != "akecld-saas-dev" ? "connector-bigcommerce-prod-portal-client-secret" : "connector-bigcommerce-dev-portal-client-secret"
  project = "akeneo-cloud"
}

