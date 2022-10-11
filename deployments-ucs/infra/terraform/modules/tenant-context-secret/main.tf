resource "random_id" "encryption_key" {
  byte_length = 32 #For AES256
}

resource "google_secret_manager_secret_version" "encryption_key_secret" {
  secret      = var.secret_id
  secret_data = random_id.encryption_key.b64_std
}
