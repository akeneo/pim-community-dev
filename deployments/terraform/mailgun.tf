locals {
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
      http_response=$(curl -s https://api.mailgun.net/v3/domains/${self.triggers.mailgun_domain}/credentials \
        -o curl_mailgun_creation_response.txt \
        -w "%%{http_code}" \
        --user 'api:${self.triggers.mailgun_api_key}' \
        -F login='${self.triggers.mailgun_login_email}' \
        -F password='${self.triggers.mailgun_password}' \
        --retry 5 \
        --retry-delay 5 \
        --retry-max-time 40 )
      if [ "$${http_response}" != "200" ]; then
          echo "!!! ERROR - Mailgun credentials creation failed - http_response: $${http_response} !!!"
          cat curl_mailgun_creation_response.txt
          rm curl_mailgun_creation_response.txt
          exit 22
      else
          echo "Mailgun credentials creation is OK - http_response: $${http_response}"
          cat curl_mailgun_creation_response.txt
          rm curl_mailgun_creation_response.txt
      fi
EOF

  }

  provisioner "local-exec" {
    when        = destroy
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
      # If you've changed mailgun_email, this command will fail
      # Thereby, you should first do a terraform destroy of this resource with the previous mailgun_email value
      http_response=$(curl -s -X DELETE https://api.mailgun.net/v3/domains/${self.triggers.mailgun_domain}/credentials/${self.triggers.mailgun_login_email} \
        -o curl_mailgun_deletion_response.txt \
        -w "%%{http_code}" \
        --user 'api:${self.triggers.mailgun_api_key}' \
        --retry 5 \
        --retry-delay 5 \
        --retry-max-time 40 )
      if [ "$${http_response}" != "200" ]; then
          echo "!!! ERROR - Mailgun credentials destroy failed - http_response: $${http_response} !!!"
          cat curl_mailgun_deletion_response.txt
          rm curl_mailgun_deletion_response.txt
      else
          echo "Mailgun credentials deletion is OK - http_response: $${http_response} "
          cat curl_mailgun_deletion_response.txt
          rm curl_mailgun_deletion_response.txt
      fi
EOF
  }
}
