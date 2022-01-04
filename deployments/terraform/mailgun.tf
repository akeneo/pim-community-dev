locals {
  mailgun_login_name  = format("%s-%s", local.pfid, var.google_project_id)
  mailgun_login_email = format("%s@%s", local.mailgun_login_name, var.mailgun_domain)
}

resource "random_string" "mailgun_password" {
  length  = 12
  special = false
}

resource "mailgun_domain_credential" "mailgun_credential" {
  domain      = var.mailgun_domain
  login       = local.mailgun_login_name
  password    = random_string.mailgun_password.result
  region      = var.mailgun_region
}
