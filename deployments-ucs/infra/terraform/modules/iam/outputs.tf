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
