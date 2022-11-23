locals {
  secrets = { for s in var.secrets : s.name => s }
}
resource "google_secret_manager_secret" "this" {
  for_each  = local.secrets
  project   = var.project_id
  secret_id = each.value.name

  labels = each.value.labels

  replication {
    automatic = true
  }
}

resource "google_secret_manager_secret_iam_binding" "secret_accessor" {
  for_each  = local.secrets
  project   = var.project_id
  secret_id = google_secret_manager_secret.this[each.value.name].secret_id
  role      = "roles/secretmanager.secretAccessor"
  members   = local.secrets[each.value.name].members
}

resource "google_secret_manager_secret_iam_binding" "secret_version_manager" {
  for_each  = local.secrets
  project   = var.project_id
  secret_id = google_secret_manager_secret.this[each.value.name].secret_id
  role      = "roles/secretmanager.secretVersionManager"
  members   = local.secrets[each.value.name].members
}

resource "google_secret_manager_secret_iam_binding" "secret_viewer" {
  for_each  = local.secrets
  project   = var.project_id
  secret_id = google_secret_manager_secret.this[each.value.name].secret_id
  role      = "roles/secretmanager.viewer"
  members   = local.secrets[each.value.name].members
}
