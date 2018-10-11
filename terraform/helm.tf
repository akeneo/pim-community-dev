data "template_file" "helm_pim_config" {
  template = "${file("${path.module}/helm-values.tpl")}"

  vars {
    pfid                    = "${var.pfid}"
    projectId               = "${var.google_project_name}"
    googleZone              = "${var.google_project_zone}"
    pimmaster_dns_name      = "${google_dns_record_set.main.name}"
    mailgun_login           = "${var.mailgun_login}@${var.mailgun_domain}"
    mailgun_password        = "${random_string.mailgun_password.result}"
  }
}

resource "local_file" "helm_pim_config" {
  content  = "${data.template_file.helm_pim_config.rendered}"
  filename = "./terraform-values.yaml"
}