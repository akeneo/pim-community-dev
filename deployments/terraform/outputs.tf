output "google_project_id" {
  value = var.google_project_id
}

output "storage_bucket_name" {
  value = google_storage_bucket.srnt_bucket.name
}

output "storage_bucket_location" {
  value = google_storage_bucket.srnt_bucket.location
}

output "pfid" {
  value = local.pfid
}

output "dns_external" {
  value = google_dns_record_set.main.name
}

output "instance_name" {
  value = var.instance_name
}

output "tf-helm-pim-values" {
  value = data.template_file.helm_pim_config.rendered
}

output "helm_exec_id" {
  description = "Provide the ID in order to allow other modules to depends on it"
  value       = null_resource.helm_release_pim.id
}
