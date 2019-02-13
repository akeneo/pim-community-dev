resource "google_monitoring_uptime_check_config" "https" {
  display_name = "${replace(var.dns_external, "/\\.$/", "")}"
  timeout      = "60s"
  project      = "${var.google_project_name}"

  http_check = {
    path    = "/user/login"
    port    = "443"
    use_ssl = true
  }

  monitored_resource {
    type = "uptime_url"

    labels = {
      project_id = "${var.google_project_name}"
      host       = "${replace(var.dns_external, "/\\.$/", "")}"
    }
  }
}
