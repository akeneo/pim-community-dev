output "name" {
  value = google_dns_managed_zone.public_zone.name
}

output "nameservers" {
  value = data.google_dns_record_set.nameservers.rrdatas
}
