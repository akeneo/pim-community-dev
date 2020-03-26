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

output "tf-helm-pim-values" {
  value = data.template_file.helm_pim_config.rendered
}

