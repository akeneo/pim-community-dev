resource "google_logging_metric" "login-response-time-distribution" {
  name            = "${local.pfid}-login-response-time-distribution"
  description     = "Distribution of response time on the ${var.monitoring_url} url."
  filter          = "resource.type=k8s_container AND jsonPayload.http.path=\"${var.monitoring_url}\" AND resource.labels.namespace_name=${local.pfid}"
  value_extractor = "EXTRACT(jsonPayload.http.duration_micros)"
  label_extractors = {
    "response_code" = "EXTRACT(jsonPayload.http.response_code)"
  }
  bucket_options {
    exponential_buckets {
      num_finite_buckets = 20
      growth_factor      = 2
      scale              = 5
    }
  }
  metric_descriptor {
    metric_kind = "DELTA"
    value_type  = "DISTRIBUTION"
    unit        = "us"
    labels {
      key        = "response_code"
      value_type = "INT64"
    }
  }
}

resource "google_monitoring_uptime_check_config" "https" {
  display_name = replace(var.dns_external, "/\\.$/", "")
  timeout      = "30s"
  period       = "60s"
  project      = var.google_project_id

  http_check {
    path    = var.monitoring_url
    port    = "443"
    headers = {
      X-AUTH-TOKEN = "${var.monitoring_authentication_token}"
    }
    use_ssl = true
    validate_ssl = false
  }

  monitored_resource {
    type = "uptime_url"

    labels = {
      project_id = var.google_project_id
      host       = replace(var.dns_external, "/\\.$/", "")
    }
  }
  depends_on = [
    var.helm_exec_id
  ]
}

resource "google_monitoring_notification_channel" "pagerduty" {
  project      = var.google_project_id
  display_name = "Notification channel of ${local.pfid}"
  type         = "pagerduty"

  labels = {
    service_key = var.pager_duty_service_key
  }
}
