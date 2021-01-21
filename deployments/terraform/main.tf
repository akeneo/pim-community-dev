terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
  }
}

provider "google" {
  project = var.google_project_id
  version = ">= 3.21.0, < 4.0.0"
}

provider "helm" {
  version = ">= 0.10.5, < 1.0.0"
  kubernetes {
    config_path = ".kubeconfig"
  }
}

locals {
  type                = "${replace(var.product_reference_type, "growth_", "") != var.product_reference_type}" ? "grth" : "srnt"
  pfid                = "${local.type}-${var.instance_name}"
  mailgun_login_name  = format("%s-%s", local.pfid, var.google_project_id)
  mailgun_login_email = "${local.mailgun_login_name}@${var.mailgun_domain}"
}

resource "random_string" "mailgun_password" {
  length  = 12
  special = false
}

resource "null_resource" "mailgun_credential" {
  triggers = {
    mailgun_password    = random_string.mailgun_password.result
    mailgun_login_email = local.mailgun_login_email
    mailgun_domain      = var.mailgun_domain
    mailgun_api_key     = var.mailgun_api_key
  }

  provisioner "local-exec" {
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
curl -s --user 'api:${self.triggers.mailgun_api_key}' \
		https://api.mailgun.net/v3/domains/${self.triggers.mailgun_domain}/credentials \
		-F login='${self.triggers.mailgun_login_email}' \
		-F password='${random_string.mailgun_password.result}'
EOF

  }

  provisioner "local-exec" {
    when        = destroy
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
# If you've changed mailgun_email, this command will fail
# Thereby, you should first do a terraform destroy of this resource with the previous mailgun_email value
curl -s --user 'api:${var.mailgun_api_key}' -X DELETE \
		https://api.mailgun.net/v3/domains/${self.triggers.mailgun_domain}/credentials/${self.triggers.mailgun_login_email}
EOF

  }
}

data "google_dns_managed_zone" "main" {
  name    = var.dns_zone
  project = var.dns_project
}

resource "google_dns_record_set" "main" {
  name         = var.dns_external
  type         = "CNAME"
  ttl          = 300
  managed_zone = var.dns_zone
  rrdatas      = [var.dns_internal]
  project      = var.dns_project
}
