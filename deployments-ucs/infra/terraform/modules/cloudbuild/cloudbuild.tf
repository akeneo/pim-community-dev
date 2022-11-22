resource "google_cloudbuild_trigger" "pr-trigger" {
  count           = var.trigger_on_pr ? 1 : 0
  name            = "${var.trigger_name}-plan"
  project         = var.project_id
  service_account = "projects/${var.project_id}/serviceAccounts/${var.cloudbuild_service_account}"
  filename        = var.cloudbuild_filename
  included_files  = var.cloudbuild_included_files

  github {
    owner = var.cloudbuild_github_org
    name  = var.cloudbuild_github_repository
    pull_request {
      branch = "^${var.cloudbuild_github_branch}$"
    }
  }

  include_build_logs = "INCLUDE_BUILD_LOGS_WITH_STATUS"
}

resource "google_cloudbuild_trigger" "master-trigger" {
  name            = "${var.trigger_name}-apply"
  project         = var.project_id
  service_account = "projects/${var.project_id}/serviceAccounts/${var.cloudbuild_service_account}"
  filename        = var.cloudbuild_filename
  included_files  = var.cloudbuild_included_files

  github {
    owner = var.cloudbuild_github_org
    name  = var.cloudbuild_github_repository
    push {
      branch = "^${var.cloudbuild_github_branch}$"
    }
  }

  approval_config {
    approval_required = true
  }

  include_build_logs = "INCLUDE_BUILD_LOGS_WITH_STATUS"
}
