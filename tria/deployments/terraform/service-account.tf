resource "google_service_account" "pim_service_account" {
  account_id   = format("gsa-%s", substr(md5(var.instance_name), 0, 24))
  display_name = "Google access for pim container (${var.instance_name})"
}

resource "google_service_account_key" "pim_service_account" {
  service_account_id = google_service_account.pim_service_account.id
  public_key_type    = "TYPE_X509_PEM_FILE"

  depends_on = [google_service_account.pim_service_account]
}
