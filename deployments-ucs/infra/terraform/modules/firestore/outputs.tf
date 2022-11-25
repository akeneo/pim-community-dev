output "datadog_gcp_integration_email" {
  description = "Datadog GCP integration service account email"
  value       =  google_service_account.timmy_datadog_gcp_integration.email
}

output "datadog_gcp_integration_id" {
  description = "Datadog GCP integration service account id"
  value       =  google_service_account.timmy_datadog_gcp_integration.unique_id
}
