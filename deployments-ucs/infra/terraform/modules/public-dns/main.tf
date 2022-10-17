locals {
  zone_norm = replace(var.zone_name, ".", "-")
}

resource "google_dns_managed_zone" "public_zone" {
  project     = var.project_id
  name        = local.zone_norm
  dns_name    = "${var.zone_name}."
  description = "Managed by Terraform and kubernetes.io/external-dns"
  visibility  = "public"
}

resource "random_string" "random" {
  length  = 16
  special = false
  upper   = false
}

resource "random_string" "random_cert" {
  length  = 10
  special = false
  upper   = false
  numeric = true
}

resource "google_certificate_manager_dns_authorization" "dns_authorization" {
  project     = var.project_id
  name        = "acme-${random_string.random.result}"
  description = "${var.zone_name} auth"
  domain      = var.zone_name

  lifecycle {
    create_before_destroy = false
  }
}

resource "google_dns_record_set" "dns_authorization_cname" {
  project      = var.project_id
  name         = google_certificate_manager_dns_authorization.dns_authorization.dns_resource_record[0].name
  managed_zone = google_dns_managed_zone.public_zone.name
  type         = google_certificate_manager_dns_authorization.dns_authorization.dns_resource_record[0].type
  ttl          = 30
  rrdatas      = [google_certificate_manager_dns_authorization.dns_authorization.dns_resource_record[0].data]
}

resource "google_certificate_manager_certificate" "certificate" {
  project     = var.project_id
  name        = "${local.zone_norm}-cert-${random_string.random_cert.result}"
  description = "${var.zone_name} wildcard cert"
  scope       = "DEFAULT"
  managed {
    domains = [
      google_certificate_manager_dns_authorization.dns_authorization.domain,
      "*.${var.zone_name}"
    ]
    dns_authorizations = [
      google_certificate_manager_dns_authorization.dns_authorization.id,
    ]
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "google_certificate_manager_certificate_map" "certificate_map" {
  project     = var.project_id
  name        = "${local.zone_norm}-cert-map"
  description = "${var.zone_name} certificate map"

  provider = google-beta
}

resource "google_certificate_manager_certificate_map_entry" "certificate_map_entry" {
  project      = var.project_id
  name         = "${local.zone_norm}-cert-map-entry"
  description  = "${var.zone_name} certificate map entry"
  map          = google_certificate_manager_certificate_map.certificate_map.name
  certificates = [google_certificate_manager_certificate.certificate.id]
  hostname     = "*.${var.zone_name}"

  provider = google-beta
}

resource "google_certificate_manager_certificate_map_entry" "certificate_map_entry_primary" {
  project      = var.project_id
  name         = "${local.zone_norm}-primary"
  description  = "${var.zone_name} certificate map entry"
  map          = google_certificate_manager_certificate_map.certificate_map.name
  certificates = [google_certificate_manager_certificate.certificate.id]
  matcher      = "PRIMARY"

  provider = google-beta
}
