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

resource "google_storage_bucket" "srnt_es_bucket" {
  name     = var.bucket_name != "" ? "${var.bucket_name}-es" : "${local.pfid}-es"
  location = var.google_storage_location
  count    = local.type == "tria" ? 0 : 1

  labels = {
    pfid = local.pfid
    type = "elasticsearch"
  }

  force_destroy = var.force_destroy_storage
}

resource "google_storage_bucket_iam_member" "srnt_bucket_elasticsearch_storage" {
  bucket = google_storage_bucket.srnt_es_bucket[count.index].name
  role   = "roles/storage.objectAdmin"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"
  count  = local.type == "tria" ? 0 : 1

  depends_on = [
    google_service_account.pim_service_account,
    google_storage_bucket.srnt_es_bucket,
  ]
}
