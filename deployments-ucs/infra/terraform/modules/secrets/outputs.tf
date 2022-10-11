output "google_secrets" {
  description = "Attributes of created Google secrets"
  value       = toset([for secret in google_secret_manager_secret.this : secret.name])
}

output "google_secrets_ids" {
  description = "IDS Attributes of created Google secrets"
  value       = { for key, secret in google_secret_manager_secret.this : key => secret.id }
}
