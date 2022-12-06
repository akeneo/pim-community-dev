resource "google_service_account" "timmy_cloud_function_sa" {
  project      = var.project_id
  account_id   = "timmy-cloud-function"
  display_name = "Timmy Generic cloud function"
}

resource "google_service_account_iam_binding" "timmy_cloud_function_sa_usage" {
  service_account_id = google_service_account.timmy_cloud_function_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.configconnector.email}",
  ]
}

resource "google_project_iam_custom_role" "timmy_cloud_function" {
  project     = var.project_id
  role_id     = "timmy_cloud_function.role"
  title       = "Timmy Cloud Function Role"
  description = "Role for executing Timmy cloud functions"
  permissions = [
    "cloudfunctions.functions.call",
    "cloudfunctions.functions.invoke",
    "run.jobs.run",
    "run.routes.invoke"
  ]
}

resource "google_project_iam_member" "timmy_cloud_function_log_writer" {
  project = var.project_id
  role    = "roles/logging.logWriter"
  member  = "serviceAccount:${google_service_account.timmy_cloud_function_sa.email}"
}

resource "google_project_iam_member" "timmy_firestore_sa_usage" {
  for_each = toset(var.firestore_projects_id)
  project  = each.value
  role     = "roles/firebaserules.system"
  member   = "serviceAccount:${google_service_account.timmy_cloud_function_sa.email}"
}

resource "google_project_iam_member" "timmy_cloud_function_role" {
  project = var.project_id
  role    = google_project_iam_custom_role.timmy_cloud_function.name
  member  = "serviceAccount:${google_service_account.timmy_cloud_function_sa.email}"
}

resource "google_service_account" "timmy_deploy_sa" {
  project      = var.project_id
  account_id   = "timmy-deployment"
  display_name = "Timmy deployment service account"
}

resource "google_service_account_iam_binding" "timmy_deploy_sa_usage" {
  service_account_id = google_service_account.timmy_deploy_sa.name
  role               = "roles/iam.serviceAccountUser"

  members = [
    "serviceAccount:${google_service_account.configconnector.email}",
  ]
}

resource "google_project_iam_member" "timmy_deploy_log_writer" {
  project = var.project_id
  role    = "roles/logging.logWriter"
  member  = "serviceAccount:${google_service_account.timmy_deploy_sa.email}"
}
