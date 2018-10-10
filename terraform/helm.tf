data "template_file" "helm_pim_config" {
  template = "${file("${path.module}/helm-values.tpl")}"

  vars {
    pfid                    = "${var.pfid}"
    projectId               = "${var.google_project_name}"
    googleZone              = "${var.google_project_zone}"
    mailgun_login      = "${data.template_file.akob_mailgun_login.rendered}"
    mailgun_password   = "${random_string.akob_mailgun_password.result}"
  }
}

resource "local_file" "helm_pim_config" {
  content  = "${data.template_file.helm_pim_config.rendered}"
  filename = "./pim-values.yaml"
}