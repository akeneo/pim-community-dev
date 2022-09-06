resource "google_service_account" "portal_function_sa" {
  project      = var.project_id
  account_id   = "timmy-portal-function"
  display_name = "Timmy portal request function"
}

resource "google_service_account_iam_binding" "timmy_portal_sa_usage" {
  service_account_id = google_service_account.portal_function_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.cloudconfig.email}",
  ]
}

##### Timmy Deployment
resource "google_service_account" "timmy_depl_sa" {
  project      = var.project_id
  account_id   = "timmy-deployment"
  display_name = "Timmy deployment SA"
}

resource "google_service_account_iam_binding" "timmy_depl_sa_usage" {
  service_account_id = google_service_account.timmy_depl_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.cloudconfig.email}",
  ]
}

resource "google_project_iam_member" "timmy_depl_workload_identity" {
  project = var.project_id
  role    = "roles/iam.workloadIdentityUser"
  member  = "serviceAccount:${var.project_id}.svc.id.goog[${var.timmy_k8s_ns}/${var.timmy_k8s_sa}]"
}

resource "google_project_iam_binding" "timmy_depl_log_writer" {
  project = var.project_id
  role    = "roles/logging.logWriter"
  members = [
    "serviceAccount:${google_service_account.timmy_depl_sa.email}"
  ]
}

resource "google_project_iam_binding" "timmy_depl_source_reader" {
  project = var.project_id
  role    = "roles/source.reader"
  members = [
    "serviceAccount:${google_service_account.timmy_depl_sa.email}"
  ]
}

resource "google_project_iam_binding" "timmy_depl_cloudfunction_developer" {
  project = var.project_id
  role = "roles/cloudfunctions.developer"
  members = [
    "serviceAccount:${google_service_account.timmy_depl_sa.email}"
  ]
}

resource "google_project_iam_binding" "timmy_depl_sa" {
  project = var.project_id
  role = "roles/iam.serviceAccountUser"
  members = [
    "serviceAccount:${google_service_account.timmy_depl_sa.email}"
  ]
}
