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
  member = "serviceAccount:${google_service_account.pimstorage.email}"

  depends_on = [
    google_service_account.pimstorage,
    google_storage_bucket.srnt_bucket,
  ]
}

resource "google_service_account" "pimstorage" {
  account_id   = format("gs-%s", substr(md5(var.instance_name), 0, 25))
  display_name = "Google access for pim container (${var.instance_name})"
}

resource "google_service_account_key" "pimstorage" {
  service_account_id = google_service_account.pimstorage.id
  public_key_type    = "TYPE_X509_PEM_FILE"

  depends_on = [google_service_account.pimstorage]
}

