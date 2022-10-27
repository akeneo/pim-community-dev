resource "google_compute_security_policy" "api_policy" {
  project     = var.project_id
  name        = "${var.project_id}-throttle-api"
  description = "rate limits"
  provider    = google-beta

  dynamic "rule" {
    for_each = toset(var.enable_rate_limiting_api ? [1] : [])
    content {
      action   = "throttle"
      preview  = true
      priority = "1000"
      rate_limit_options {
        conform_action = "allow"
        exceed_action  = "deny(429)"
        enforce_on_key = "ALL"
        rate_limit_threshold {
          count        = var.rate_limit_api.count
          interval_sec = var.rate_limit_api.interval_sec
        }
      }
      match {
        versioned_expr = "SRC_IPS_V1"
        config {
          src_ip_ranges = ["*"]
        }
      }
    }
  }

  # Default rule
  rule {
    action   = "allow"
    priority = "2147483647"
    match {
      versioned_expr = "SRC_IPS_V1"
      config {
        src_ip_ranges = ["*"]
      }
    }
    description = "default rule"
  }
}
