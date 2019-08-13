data "template_file" "helm_pim_config" {
  template = "${file("${path.module}/tf-helm-pim-values.tpl")}"

  vars {
    instanceName        = "${var.instance_name}"
    pfid                = "${local.pfid}"
    projectId           = "${var.google_project_id}"
    googleZone          = "${var.google_project_zone}"
    pimmaster_dns_name  = "${replace(google_dns_record_set.main.name, "/\\.$/", "")}"
    mailgun_login_email = "${data.template_file.mailgun_login.rendered}@${var.mailgun_domain}"
    mailgun_password    = "${random_string.mailgun_password.result}"
    mailgun_host        = "${var.mailgun_host}"
    mailgun_port        = "${var.mailgun_port}"
    bucketName          = "${google_storage_bucket.srnt_bucket.name}"
    pimStoragekey       = "${google_service_account_key.pimstorage.private_key}"
  }
}

resource "local_file" "helm_pim_config" {
  content  = "${data.template_file.helm_pim_config.rendered}"
  filename = "./tf-helm-pim-values.yaml"
}
