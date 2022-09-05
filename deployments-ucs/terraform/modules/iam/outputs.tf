output "gke_sa_email" {
  description = "GKE nodes service account"
  value       = google_service_account.gke.email
}

output "helm_admin_sa_email" {
  description = "Helm admin service account"
  value       = google_service_account.helm_admin.email
}

output "portal_function_sa_email" {
  description = "Timmy function service account"
  value       = google_service_account.portal_function_sa.email
}
