locals {
  cloudbuild_service_account = (var.cloudbuild_service_account != null ?
    var.cloudbuild_service_account :
  "main-service-account@${var.cloudbuild_project_id}.iam.gserviceaccount.com")
}

resource "google_cloudbuild_trigger" "trigger" {
  name               = var.trigger_name
  disabled           = var.disabled
  project            = var.cloudbuild_project_id
  service_account    = "projects/${var.cloudbuild_project_id}/serviceAccounts/${local.cloudbuild_service_account}"
  filename           = var.cloudbuild_filename
  included_files     = var.cloudbuild_included_files
  include_build_logs = "INCLUDE_BUILD_LOGS_WITH_STATUS"
  substitutions      = merge(var.substitutions, { _LOGS_BUCKET = var.logs_bucket })
  tags               = var.tags

  github {
    owner = var.cloudbuild_github_org
    name  = var.cloudbuild_github_repository
    dynamic "pull_request" {
      for_each = var.trigger_on_pr ? toset([true]) : toset([])
      content {
        branch = "^${var.cloudbuild_github_branch}$"
      }
    }
    dynamic "push" {
      for_each = var.trigger_on_push ? toset([true]) : toset([])
      content {
        branch = "^${var.cloudbuild_github_branch}$"
      }
    }
  }

  approval_config {
    approval_required = var.approval_required
  }
}
