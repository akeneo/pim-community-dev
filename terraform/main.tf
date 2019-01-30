terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
  }
}

data "template_file" "mailgun_login" {
  template = "${format ("%s-%s", var.pfid, var.google_project_name)}"
}

resource "random_string" "mailgun_password" {
  length  = 12
  special = false
}

resource "null_resource" "mailgun_credential" {
  provisioner "local-exec" {
    command = <<EOF
curl -s --user 'api:${var.mailgun_api_key}' \
		https://api.mailgun.net/v3/domains/${var.mailgun_domain}/credentials \
		-F login='${data.template_file.mailgun_login.rendered}@${var.mailgun_domain}' \
		-F password='${random_string.mailgun_password.result}' ; \
EOF
  }

  provisioner "local-exec" {
    when = "destroy"

    command = <<EOF
curl -s --user 'api:${var.mailgun_api_key}' -X DELETE \
		https://api.mailgun.net/v3/domains/${var.mailgun_domain}/credentials/${data.template_file.mailgun_login.rendered}@${var.mailgun_domain}
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
