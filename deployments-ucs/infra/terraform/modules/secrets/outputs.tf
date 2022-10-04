output "google_secrets" {
  description = "Attributes of created Google secrets"
  value       = toset([for secret in google_secret_manager_secret.this : secret.name])
}
