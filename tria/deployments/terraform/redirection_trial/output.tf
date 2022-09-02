### compute_backend_service
output "google_compute_backend_service_backend_service_self_link" {
  value = google_compute_backend_service.backend_service.self_link
}

### compute_global_address
output "google_compute_global_address_external_ip_self_link" {
  value = google_compute_global_address.external_ip.self_link
}

### compute_global_forwarding_rule
output "google_compute_global_forwarding_rule_auto_self_link" {
  value = google_compute_global_forwarding_rule.forwarding_rule_auto.self_link
}

output "google_compute_global_forwarding_rule_frontend_self_link" {
  value = google_compute_global_forwarding_rule.forwarding_rule_frontend.self_link
}

### compute_ssl_policy
output "google_compute_ssl_policy_ssl_policy_self_link" {
  value = google_compute_ssl_policy.ssl_policy.self_link
}

### compute_target_http_proxy
output "google_compute_target_http_proxy_lb_target_proxy_http_self_link" {
  value = google_compute_target_http_proxy.lb_target_proxy_http.self_link
}

### compute_target_https_proxy
output "google_compute_target_https_proxy_lb_target_proxy_https_self_link" {
  value = google_compute_target_https_proxy.lb_target_proxy_https.self_link
}

### compute_url_map
output "google_compute_url_map_lb_self_link" {
  value = google_compute_url_map.lb.self_link
}
