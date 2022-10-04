resource "google_service_account" "external_dns" {
  project      = var.project_id
  account_id   = "external-dns"
  display_name = "External DNS service account"
}

resource "google_project_iam_member" "external_dns_binding" {
  project = var.project_id
  role    = "roles/dns.admin"
  member  = "serviceAccount:${google_service_account.external_dns.email}"
}

resource "google_project_iam_member" "external_dns_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[${var.external_dns_k8s_ns}/${var.external_dns_k8s_sa}]"
}
