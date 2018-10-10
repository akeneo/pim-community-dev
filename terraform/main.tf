resource "random_string" "mailgun_password" {
  length  = 12
  special = false
}

resource "null_resource" "mailgun-credential" {
  provisioner "local-exec" {
    command = <<EOF
curl -s --user 'api:${var.MAILGUN_API_KEY}' \
		https://api.mailgun.net/v3/domains/${var.MAILGUN_CLOUD_DOMAIN}/credentials \
		-F login='${var.mailgun_login}@${var.mailgun_domain}' \
		-F password='${random_string.mailgun_password.result}' ; \
EOF
  }

  provisioner "local-exec" {
    when = "destroy"

    command = <<EOF
curl -s --user 'api:${var.MAILGUN_API_KEY}' -X DELETE \
		https://api.mailgun.net/v3/domains/${var.MAILGUN_CLOUD_DOMAIN}/credentials/${var.mailgun_login}@${var.mailgun_domain}
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
