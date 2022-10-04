data "google_dns_record_set" "nameservers" {
  project      = var.project_id
  managed_zone = google_dns_managed_zone.public_zone.name
  name         = google_dns_managed_zone.public_zone.dns_name
  type         = "NS"
}

resource "google_dns_record_set" "forward" {
  count   = var.forward == null ? 0 : 1
  project = var.forward.target_project_id
  name    = google_dns_managed_zone.public_zone.dns_name
  type    = "NS"
  ttl     = 300

  managed_zone = var.forward.target_zone_name
  rrdatas      = data.google_dns_record_set.nameservers.rrdatas
}
