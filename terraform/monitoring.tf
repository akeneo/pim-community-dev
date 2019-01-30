resource "google_monitoring_uptime_check_config" "http" {
  display_name  = "http-uptime-check"
  timeout       = "60s"
  project       = "${var.google_project_name}"

  http_check = {
    path = "/user/login"
    port = "443"
  }

  monitored_resource {
    type = "uptime_url"

    labels = {
      project_id = "${var.google_project_name}"
      host       = "${var.dns_external}"
    }
  }
}
