resource "google_project_iam_custom_role" "argocd_role" {
  project     = var.project_id
  role_id     = "argocd.role"
  title       = "ArgoCD GKE Role"
  description = "Role for using ArgoCD with Google Buckets"
  permissions = [
    "storage.buckets.create",
    "storage.buckets.get",
    "storage.buckets.getIamPolicy",
    "storage.buckets.setIamPolicy",
    "storage.objects.get",
    "storage.objects.list",
  ]
}

resource "google_service_account" "argocd" {
  project      = var.project_id
  account_id   = "argocd"
  display_name = "ArgoCD service account"
}

resource "google_project_iam_binding" "argocd_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.argocd_role.name

  members = [
    "serviceAccount:${google_service_account.argocd.email}"
  ]
}

resource "google_project_iam_member" "argocd_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[argocd/argocd-repo-server]"
}

resource "google_secret_manager_secret" "argocd_token" {
  project   = var.project_id
  secret_id = "ARGOCD_TOKEN"

  labels = {
    usage = "argocd"
  }

  replication {
    automatic = true
  }
}

resource "google_secret_manager_secret_iam_binding" "argocd_token_ci" {
  project   = var.project_id
  secret_id = google_secret_manager_secret.argocd_token.secret_id
  role      = "roles/secretmanager.secretAccessor"
  members   = [
    local.ci_sa,
    "serviceAccount:${google_service_account.cluster_bootstrap.email}"
  ]
}

resource "google_secret_manager_secret_iam_binding" "argocd_token_admins" {
  project   = var.project_id
  secret_id = google_secret_manager_secret.argocd_token.secret_id
  role      = "roles/secretmanager.secretVersionManager"
  members   = var.secrets_admins
}
