
### dns record
resource "google_dns_record_set" "main" {
  name         = "*.${var.source_domain}."
  type         = "A"
  ttl          = 300
  managed_zone = replace(var.dns_zone, ".", "-")
  rrdatas      = [google_compute_global_address.external_ip.address]
  project      = var.dns_project
}

### secret_manager
data "google_secret_manager_secret_version" "private_key" {
  project = var.google_project_secret
  secret  = "private-key-${replace(var.source_domain, ".", "-")}"
}
data "google_secret_manager_secret_version" "public-cert-pem" {
  project = var.google_project_secret
  secret  = "public-cert-pem-${replace(var.source_domain, ".", "-")}"
}

### compute_ssl_certificate
resource "google_compute_ssl_certificate" "ssl_certificate" {
  name_prefix = var.prefix_resources
  description = "${var.prefix_resources}-ssl-certificate"
  private_key = data.google_secret_manager_secret_version.private_key.secret_data
  certificate = data.google_secret_manager_secret_version.public-cert-pem.secret_data
  project     = var.google_project_id

  lifecycle {
    create_before_destroy = true
  }
}


### compute_url_map
resource "google_compute_url_map" "lb" {
  default_url_redirect {
    host_redirect          = var.redirection_url
    https_redirect         = false
    redirect_response_code = "MOVED_PERMANENTLY_DEFAULT"
    strip_query            = false
  }

  name    = "${var.prefix_resources}-lb"
  project = var.google_project_id
}


### compute_target_https_proxy
resource "google_compute_target_https_proxy" "lb_target_proxy_https" {
  name             = "${var.prefix_resources}-lb-target-proxy-https"
  project          = var.google_project_id
  proxy_bind       = false
  quic_override    = "NONE"
  ssl_certificates = [google_compute_ssl_certificate.ssl_certificate.self_link]
  ssl_policy       = google_compute_ssl_policy.ssl_policy.self_link
  url_map          = google_compute_url_map.lb.self_link

}


### compute_target_http_proxy
resource "google_compute_target_http_proxy" "lb_target_proxy_http" {
  name       = "${var.prefix_resources}-lb-target-proxy-http"
  project    = var.google_project_id
  proxy_bind = false
  url_map    = google_compute_url_map.lb.self_link
}


### compute_ssl_policy
resource "google_compute_ssl_policy" "ssl_policy" {
  description     = "${var.prefix_resources}-ssl-policy"
  min_tls_version = "TLS_1_0"
  name            = "${var.prefix_resources}-ssl-policy"
  profile         = "COMPATIBLE"
  project         = var.google_project_id
}


### google_compute_global_forwarding_rule
resource "google_compute_global_forwarding_rule" "forwarding_rule_auto" {
  ip_address            = google_compute_global_address.external_ip.address
  ip_protocol           = "TCP"
  load_balancing_scheme = "EXTERNAL"
  name                  = "${var.prefix_resources}-forwarding-rule-auto"
  port_range            = "443-443"
  project               = var.google_project_id
  target                = google_compute_target_https_proxy.lb_target_proxy_https.self_link
}

resource "google_compute_global_forwarding_rule" "forwarding_rule_frontend" {
  ip_address            = google_compute_global_address.external_ip.address
  ip_protocol           = "TCP"
  load_balancing_scheme = "EXTERNAL"
  name                  = "${var.prefix_resources}-forwarding-rule-frontend"
  port_range            = "80-80"
  project               = var.google_project_id
  target                = google_compute_target_http_proxy.lb_target_proxy_http.self_link
}

### compute_backend_service
resource "google_compute_backend_service" "backend_service" {
  affinity_cookie_ttl_sec         = 0
  connection_draining_timeout_sec = 0
  description                     = "${var.prefix_resources}-backend-service"
  enable_cdn                      = false
  load_balancing_scheme           = "EXTERNAL"

  log_config {
    enable      = false
    sample_rate = 0
  }

  name             = "${var.prefix_resources}-backend-service"
  port_name        = "http"
  project          = var.google_project_id
  protocol         = "HTTP"
  session_affinity = "NONE"
  timeout_sec      = 30
}


### compute_global_address
resource "google_compute_global_address" "external_ip" {
  address_type  = "EXTERNAL"
  description   = "${var.prefix_resources}-external-ip"
  ip_version    = "IPV4"
  name          = "${var.prefix_resources}-external-ip"
  prefix_length = 0
  project       = var.google_project_id
}
