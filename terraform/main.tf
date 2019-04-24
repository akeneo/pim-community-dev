terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
  }
}

provider "google" {
  project = "${var.google_project_id}"
  version = "~> 2.4.0"
}

locals {
  pfid = "srnt-${var.instance_name}"
}

data "template_file" "mailgun_login" {
  template = "${format ("%s-%s", local.pfid, var.google_project_id)}"
}

resource "random_string" "mailgun_password" {
  length  = 12
  special = false
}

locals {
  mailgun_login_email = "${data.template_file.mailgun_login.rendered}@${var.mailgun_domain}"
}

resource "null_resource" "mailgun_credential" {
  triggers {
    mailgun_password    = "${random_string.mailgun_password.result}"
    mailgun_login_email = "${local.mailgun_login_email}"
    mailgun_domain      = "${var.mailgun_domain}"
  }

  provisioner "local-exec" {
    command = <<EOF
curl -s --user 'api:${var.mailgun_api_key}' \
		https://api.mailgun.net/v3/domains/${var.mailgun_domain}/credentials \
		-F login='${local.mailgun_login_email}' \
		-F password='${random_string.mailgun_password.result}'
EOF
  }

  provisioner "local-exec" {
    when = "destroy"

    command = <<EOF
# If you've changed mailgun_email, this command will fail
# Thereby, you should first do a terraform destroy of this resource with the previous mailgun_email value
curl -s --user 'api:${var.mailgun_api_key}' -X DELETE \
		https://api.mailgun.net/v3/domains/${var.mailgun_domain}/credentials/${local.mailgun_login_email}
EOF
  }
}

resource "google_dns_record_set" "main" {
  name         = "${var.dns_external}"
  type         = "CNAME"
  ttl          = 300
  managed_zone = "${var.dns_zone}"
  rrdatas      = ["${var.dns_internal}"]
  project      = "${var.dns_project}"
}
