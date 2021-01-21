
output "dns_external" {
  value = google_dns_record_set.main.name
}

output "google_project_id" {
  value = var.google_project_id
}

output "helm_exec_id" {
  description = "Provide the ID in order to allow other modules to depends on it"
  value       = null_resource.helm_release_pim.id
}

output "instance_name" {
  value = var.instance_name
}

output "pfid" {
  value = local.pfid
}

output "product_reference_code" {
  value = var.product_reference_code
}

output "product_reference_type" {
  value = var.product_reference_type
}

output "storage_bucket_name" {
  value = google_storage_bucket.srnt_bucket.name
}

output "storage_bucket_location" {
  value = google_storage_bucket.srnt_bucket.location
}

output "tf-helm-pim-values" {
  value = data.template_file.helm_pim_config.rendered
}

output "type" {
  value = local.type
}
