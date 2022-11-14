data "google_secret_manager_secret_version" "performance_analytics_service_account_key" {
  secret  = "performance-analytics-service-account-key"
  project = var.google_project_id
}
