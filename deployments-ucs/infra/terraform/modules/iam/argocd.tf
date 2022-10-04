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
