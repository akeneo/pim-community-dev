resource "google_compute_disk" "mysql-disk" {
  name        = var.mysql_disk_name != "" ? var.mysql_disk_name : "${local.pfid}-mysql"
  type        = var.mysql_disk_type
  zone        = var.google_project_zone
  size        = var.mysql_disk_size
  snapshot    = var.mysql_source_snapshot
  description = var.mysql_disk_description

  labels = {
    pfid        = local.pfid
    pim_version = lower(var.pim_version)
    app         = "mysql"
    type        = local.type
    product_reference_code = var.product_reference_code
    product_reference_type = var.product_reference_type
  }

  lifecycle {
    prevent_destroy = true
  }
}
