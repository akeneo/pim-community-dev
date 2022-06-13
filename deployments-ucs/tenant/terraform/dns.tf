# DNS isn't managed for the POC
# data "google_dns_managed_zone" "main" {
#   name    = var.dns_zone
#   project = var.dns_project
# }
#
# resource "google_dns_record_set" "main" {
#   name         = var.dns_external
#   type         = "CNAME"
#   ttl          = 300
#   managed_zone = var.dns_zone
#   rrdatas      = [var.dns_internal]
#   project      = var.dns_project
# }
