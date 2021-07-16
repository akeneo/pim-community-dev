resource "google_storage_bucket" "srnt_bucket" {
  name     = var.bucket_name != "" ? var.bucket_name : local.pfid
  location = var.google_storage_location

  labels = {
    pfid = local.pfid
  }

  force_destroy = var.force_destroy_storage
}

resource "google_storage_bucket_iam_member" "srnt_bucket_pimstorage" {
  bucket = google_storage_bucket.srnt_bucket.name
  role   = "roles/storage.objectAdmin"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_storage_bucket.srnt_bucket,
  ]
}
