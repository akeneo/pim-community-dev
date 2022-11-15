locals {
  enable = var.enable ? 1 : 0
}

resource "google_cloud_scheduler_job" "this" {
  count            = local.enable
  project          = var.project_id
  region           = var.region
  name             = var.name
  description      = var.description
  schedule         = var.schedule
  time_zone        = var.time_zone
  attempt_deadline = var.attempt_deadline

  retry_config {
    retry_count = var.retry_count
  }

  http_target {
    http_method = var.http_method
    uri         = var.http_target_uri
    body        = base64encode(var.http_target_body)
    headers     = var.http_target_headers

    oidc_token {
      service_account_email = var.oidc_service_account_email
      audience              = var.oidc_token_audience
    }
  }
}
