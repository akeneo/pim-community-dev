output "gke_sa_email" {
  description = "GKE nodes service account"
  value       = google_service_account.gke.email
}
