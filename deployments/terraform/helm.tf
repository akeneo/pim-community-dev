data "template_file" "helm_pim_config" {
  template = "${file("${path.module}/tf-helm-pim-values.tpl")}"

  vars {
    instanceName        = "${var.instance_name}"
    pfid                = "${local.pfid}"
    projectId           = "${var.google_project_id}"
    googleZone          = "${var.google_project_zone}"
    pimmaster_dns_name  = "${replace(google_dns_record_set.main.name, "/\\.$/", "")}"
    dnsZone             = "${replace(data.google_dns_managed_zone.main.dns_name, "/\\.$/", "")}"
    mailgun_login_email = "${data.template_file.mailgun_login.rendered}@${var.mailgun_domain}"
    mailgun_password    = "${random_string.mailgun_password.result}"
    mailgun_host        = "${var.mailgun_host}"
    mailgun_port        = "${var.mailgun_port}"
    bucketName          = "${google_storage_bucket.srnt_bucket.name}"
    pimStoragekey       = "${google_service_account_key.pimstorage.private_key}"
    papoProjectCode     = "${var.papo_project_code}"
  }
}

data "helm_repository" "stable" {
  name = "stable"
  url  = "https://kubernetes-charts.storage.googleapis.com"
}

data "helm_repository" "akeneo-charts" {
  name = "akeneo-charts"
  url  = "gs://akeneo-charts"
}

resource "null_resource" "helm_dependencies_update" {
  provisioner "local-exec" {
    interpreter = ["/usr/bin/env", "bash", "-c"]
    command = <<EOF
helm dependencies update ${path.module}/../pim/
EOF
  }
}

resource "helm_release" "pim" {
  name      = "${local.pfid}"
  chart     = "${path.module}/../pim/"
  namespace = "${local.pfid}"
  timeout   = "1501"
  depends_on   = ["null_resource.helm_dependencies_update"]

  values = [
    "${file("values.yaml")}",
    "${data.template_file.helm_pim_config.rendered}",
  ]

}
