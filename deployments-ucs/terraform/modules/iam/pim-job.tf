resource "google_service_account" "pim_job_sa" {
  project      = var.project_id
  account_id   = "pim-job"
  display_name = "PIM Job"
}


resource "google_service_account_iam_binding" "pim_sa_usage" {
  service_account_id = google_service_account.pim_job_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.cloudconfig.email}",
    "serviceAccount:${google_service_account.crossplane.email}",
    "serviceAccount:ucs-crossplane-test-account@${var.project_id}.iam.gserviceaccount.com" ## To be removed
  ]
}

##### PIM Deployment
resource "google_service_account" "pim_depl_sa" {
  project      = var.project_id
  account_id   = "pim-deployment"
  display_name = "PIM deployment SA"
}


resource "google_project_iam_member" "pim_depl_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[${var.pim_k8s_ns}/${var.pim_k8s_sa}]"
}