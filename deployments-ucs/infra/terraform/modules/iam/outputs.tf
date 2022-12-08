output "gke_sa_email" {
  description = "GKE nodes service account"
  value       = google_service_account.gke.email
}

output "cluster_bootstrap_sa_email" {
  description = "Cluster bootstrap service account"
  value       = google_service_account.cluster_bootstrap.email
}

output "portal_function_sa_email" {
  description = "Timmy function service account"
  value       = google_service_account.timmy_cloud_function_sa.email
}

output "pim_sa_email" {
  description = "PIM service account"
  value       = google_service_account.pim_sa.email
}

output "datadog_gcp_integration_email" {
  description = "Datadog GCP integration service account email"
  value       = google_service_account.datadog_gcp_integration.email
}

output "datadog_gcp_integration_id" {
  description = "Datadog GCP integration service account id"
  value       = google_service_account.datadog_gcp_integration.unique_id
}
