resource "google_dns_managed_zone" "private_zone" {
  project     = var.project_id
  name        = replace(var.zone_name, ".", "-")
  dns_name    = "${var.zone_name}."
  description = "Managed by Terraform and kubernetes.io/external-dns"

  visibility = "private"

  dynamic "private_visibility_config" {
    for_each = toset(length(var.networks) == 0 ? [] : [1])
    content {
      dynamic "networks" {
        for_each = toset(var.networks)
        content {
          network_url = networks.key
        }
      }
    }
  }

}