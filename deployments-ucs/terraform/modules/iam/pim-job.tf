resource "google_service_account" "pim_job_sa" {
  project      = var.project_id
  account_id   = "pim-job-function"
  display_name = "PIM-Job Cloud Function"
}

resource "google_project_iam_member" "pim_sa_firestore" {
  project = var.firestore_project_id
  role    = "roles/firebaserules.system"
  member  = "serviceAccount:${google_service_account.pim_job_sa.email}"

}

resource "google_service_account_iam_binding" "pim_sa_usage" {
  service_account_id = google_service_account.pim_job_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.cloudconfig.email}",
  ]
}

##### PIM Deployment
resource "google_service_account" "pim_job_depl_sa" {
  project      = var.project_id
  account_id   = "pim-job-deployment"
  display_name = "PIM deployment SA"
}
