resource "random_string" "monitoring_authentication_token" {
  length  = 32
  special = false
}

resource "local_file" "helm_pim_config" {
  content = templatefile("${path.module}/tf-helm-pim-values.tpl",
    {
      google_cloud_project = {
        id   = var.google_project_id
        zone = var.google_project_zone
      }

      mailgun = {
        login_email = local.mailgun_login_email
        password    = random_string.mailgun_password.result
        host        = var.mailgun_host
        port        = var.mailgun_port
      }

      pim = {
        type                            = local.type
        pfid                            = local.pfid
        version                         = var.pim_version
        dns_record                      = var.instance_name
        dns_zone                        = replace(data.google_dns_managed_zone.main.dns_name, "/\\.$/", "")
        master_dns_name                 = replace(google_dns_record_set.main.name, "/\\.$/", "")
        storage_key                     = google_service_account_key.pim_service_account.private_key
        bucket_name                     = google_storage_bucket.srnt_bucket.name
        monitoring_authentication_token = local.monitoring_authentication_token

        subscription = {
          webhook                    = google_pubsub_subscription.webhook.name
          job_queue_ui               = google_pubsub_subscription.job-queue-ui.name
          job_queue_import_export    = google_pubsub_subscription.job-queue-import-export.name
          job_queue_data_maintenance = google_pubsub_subscription.job-queue-data-maintenance.name
        }
        topic = {
          business_event             = google_pubsub_topic.business-event.name
          job_queue_ui               = google_pubsub_topic.job-queue-ui.name
          job_queue_import_export    = google_pubsub_topic.job-queue-import-export.name
          job_queue_data_maintenance = google_pubsub_topic.job-queue-data-maintenance.name
        }
      }

      portal = {
        project_code = var.papo_project_code
        project_code_truncated = substr(
          var.papo_project_code,
          0,
          min(63, length(var.papo_project_code)),
        )
        project_code_hashed = md5(var.papo_project_code)
      }

      mysql = {
        disk_name          = google_compute_disk.mysql-disk.name
        disk_size          = google_compute_disk.mysql-disk.size
        disk_storage_class = google_compute_disk.mysql-disk.type == "pd-ssd" ? "ssd-retain" : "standard-retain"
      }

      ft_catalog = {
        api_client_id = var.ft_catalog_api_client_id
        api_password  = var.ft_catalog_api_password
        api_secret    = var.ft_catalog_api_secret
        akeneo_connect = {
          saml = {
            entity_id             = var.akeneo_connect_saml_entity_id
            certificate           = var.akeneo_connect_saml_certificate
            sp_certificate_base64 = var.akeneo_connect_saml_sp_certificate_base64
            sp_private_key_base64 = var.akeneo_connect_saml_sp_private_key_base64
          }
          api = {
            client_secret   = var.akeneo_connect_api_client_secret
            client_password = var.akeneo_connect_api_client_password
          }
        }
      }

      bigcommerce_connector = {
        enabled      = local.type != "tria" ? true : false
        topic        = local.type != "tria" ? google_pubsub_topic.connector_bigcommerce[0].name : "fakeValue"
        subscription = local.type != "tria" ? google_pubsub_subscription.connector_bigcommerce[0].name : "fakeValue"
        akeneo_connect = {
          bot_password      = local.type != "tria" ? data.google_secret_manager_secret_version.bigcommerce_connector_akeneo_connect_bot_password[0].secret_data : "fakeValue"
          bot_client_secret = local.type != "tria" ? data.google_secret_manager_secret_version.bigcommerce_connector_akeneo_connect_bot_client_secret[0].secret_data : "fakeValue"
        }
      }
    }
  )
  filename = "./tf-helm-pim-values.yaml"
}

resource "null_resource" "helm_release_pim" {
  triggers = {
    values             = filebase64("./values.yaml")
    tf-helm-pim-values = local_file.helm_pim_config.content_base64
  }

  depends_on = [
    local_file.kubeconfig,
    google_storage_bucket_iam_member.srnt_bucket_pimstorage,
  ]

  provisioner "local-exec" {
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
yq w -i ${path.module}/pim/Chart.yaml version 0.0.0-${var.pim_version}
yq w -i ${path.module}/pim/Chart.yaml appVersion ${var.pim_version}
helm3 repo add akeneo-charts gs://akeneo-charts/ 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
helm3 dependencies update ${path.module}/pim/ 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
export KUBECONFIG="${local_file.kubeconfig.filename}"
helm3 upgrade --atomic --cleanup-on-fail --history-max 5 --create-namespace --wait --install --timeout 20m ${local.pfid} --namespace ${local.pfid} ${path.module}/pim/ -f tf-helm-pim-values.yaml -f values.yaml
EOF
  }
}
