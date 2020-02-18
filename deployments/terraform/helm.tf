data "template_file" "helm_pim_config" {
  template = "${file("${path.module}/tf-helm-pim-values.tpl")}"

  vars {
    instanceName                    = "${var.instance_name}"
    pfid                            = "${local.pfid}"
    projectId                       = "${var.google_project_id}"
    googleZone                      = "${var.google_project_zone}"
    pimmaster_dns_name              = "${replace(google_dns_record_set.main.name, "/\\.$/", "")}"
    dnsZone                         = "${replace(data.google_dns_managed_zone.main.dns_name, "/\\.$/", "")}"
    mailgun_login_email             = "${data.template_file.mailgun_login.rendered}@${var.mailgun_domain}"
    mailgun_password                = "${random_string.mailgun_password.result}"
    mailgun_host                    = "${var.mailgun_host}"
    mailgun_port                    = "${var.mailgun_port}"
    bucketName                      = "${google_storage_bucket.srnt_bucket.name}"
    pimStoragekey                   = "${google_service_account_key.pimstorage.private_key}"
    papoProjectCode                 = "${var.papo_project_code}"
    papoProjectCodeTruncated        = "${substr(var.papo_project_code, 0, min(63, length(var.papo_project_code)))}"
    papoProjectCodeHashed           = "${md5(var.papo_project_code)}"
    pimVersion                      = "${var.pim_version}"
    monitoring_authentication_token = "${random_string.monitoring_authentication_token.result}"
  }
}

resource "local_file" "helm_pim_config" {
  content  = "${data.template_file.helm_pim_config.rendered}"
  filename = "./tf-helm-pim-values.yaml"
}

data "helm_repository" "stable" {
  name = "stable"
  url  = "https://kubernetes-charts.storage.googleapis.com"
}

data "helm_repository" "akeneo-charts" {
  name = "akeneo-charts"
  url  = "gs://akeneo-charts"
}

resource "null_resource" "helm_release_pim" {
  triggers {
    values             = "${file("./values.yaml")}"
    tf-helm-pim-values = "${data.template_file.helm_pim_config.rendered}"
  }

  depends_on = [
    "local_file.helm_pim_config",
    "local_file.kubeconfig",
  ]

  provisioner "local-exec" {
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
helm dependencies update ${path.module}/pim/
yq w -i ${path.module}/pim/Chart.yaml version ${var.pim_version}
yq w -i ${path.module}/pim/Chart.yaml appVersion ${var.pim_version}
export KUBECONFIG="${local_file.kubeconfig.filename}"
helm upgrade --wait --install --force --timeout 1501 ${local.pfid} --namespace ${local.pfid} ${path.module}/pim/ -f tf-helm-pim-values.yaml -f values.yaml
EOF
  }
}
