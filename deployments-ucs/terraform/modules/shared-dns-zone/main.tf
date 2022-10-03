locals {
  zone_norm = replace(var.zone_name, ".", "-")
}

resource "google_dns_managed_zone" "pim_zone" {
  project     = var.project_id
  name        = local.zone_norm
  dns_name    = "${var.zone_name}."
  visibility  = "public"
  description = "${var.zone_name} -- Managed by terraform"
  labels = {
    managed_by = "terraform"
  }
  dnssec_config {
     state = "on"
  }
}

resource "google_project_iam_custom_role" "forward_role" {
  project     = var.project_id
  role_id     = "dnsFormwardRole"
  title       = "DNS Forwading role"
  description = "DNS Forwading role"
  permissions = [
    "dns.managedZones.get",
    "dns.managedZones.list",
    "dns.resourceRecordSets.create",
    "dns.resourceRecordSets.delete",
    "dns.resourceRecordSets.get",
    "dns.resourceRecordSets.list",
    "dns.resourceRecordSets.update"
  ]
}

resource "google_project_iam_member" "forward_role_member" {
  project  = var.project_id
  for_each = toset(var.admins)
  role     = google_project_iam_custom_role.forward_role.name
  member   = each.value
}
