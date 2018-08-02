data "template_file" "mailgun_login" {
  template = "${format ("%s-%s@mg.cloud.akeneo.com", var.pfid, data.terraform_remote_state.saas-cluster.env_name)}"
}

resource "random_string" "mailgun_password" {
  length  = 12
  special = false
}

resource "null_resource" "mailgun-credential" {
  provisioner "local-exec" {
    command = <<EOF
curl -s --user 'api:${var.MAILGUN_API_KEY}' \
		https://api.mailgun.net/v3/domains/${var.MAILGUN_CLOUD_DOMAIN}/credentials \
		-F login='${data.template_file.mailgun_login.rendered}' \
		-F password='${random_string.mailgun_password.result}' ; \
EOF
  }

  provisioner "local-exec" {
    when = "destroy"

    command = <<EOF
curl -s --user 'api:${var.MAILGUN_API_KEY}' -X DELETE \
		https://api.mailgun.net/v3/domains/${var.MAILGUN_CLOUD_DOMAIN}/credentials/${data.template_file.mailgun_login.rendered}
EOF
  }
}
