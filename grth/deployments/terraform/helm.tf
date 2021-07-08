resource "random_string" "monitoring_authentication_token" {
  length  = 32
  special = false
}

data "template_file" "helm_pim_config" {
  template = file("${path.module}/tf-helm-pim-values.tpl")

  vars = {
    instanceName        = var.instance_name
    type                = local.type
    pfid                = local.pfid
    projectId           = var.google_project_id
    googleZone          = var.google_project_zone
    pimmaster_dns_name  = replace(google_dns_record_set.main.name, "/\\.$/", "")
    dnsZone             = replace(data.google_dns_managed_zone.main.dns_name, "/\\.$/", "")
    mailgun_login_email = "${local.mailgun_login_email}"
    mailgun_password    = random_string.mailgun_password.result
    mailgun_host        = var.mailgun_host
    mailgun_port        = var.mailgun_port
    bucketName          = google_storage_bucket.srnt_bucket.name
    pimStoragekey       = google_service_account_key.pim_service_account.private_key
    papoProjectCode     = var.papo_project_code
    papoProjectCodeTruncated = substr(
      var.papo_project_code,
      0,
      min(63, length(var.papo_project_code)),
    )
    papoProjectCodeHashed                   = md5(var.papo_project_code)
    pimVersion                              = var.pim_version
    monitoring_authentication_token         = local.monitoring_authentication_token
    mysql_disk_name                         = google_compute_disk.mysql-disk.name
    mysql_disk_size                         = google_compute_disk.mysql-disk.size
    mysql_disk_storage_class                = google_compute_disk.mysql-disk.type == "pd-ssd" ? "ssd-retain" : "standard-retain"
    subscription_webhook                    = google_pubsub_subscription.webhook.name
    subscription_job_queue_ui               = google_pubsub_subscription.job-queue-ui.name
    subscription_job_queue_import_export    = google_pubsub_subscription.job-queue-import-export.name
    subscription_job_queue_data_maintenance = google_pubsub_subscription.job-queue-data-maintenance.name
    topic_business_event                    = google_pubsub_topic.business-event.name
    topic_job_queue_ui                      = google_pubsub_topic.job-queue-ui.name
    topic_job_queue_import_export           = google_pubsub_topic.job-queue-import-export.name
    topic_job_queue_data_maintenance        = google_pubsub_topic.job-queue-data-maintenance.name
  }
}

resource "local_file" "helm_pim_config" {
  content  = data.template_file.helm_pim_config.rendered
  filename = "./tf-helm-pim-values.yaml"
}

resource "null_resource" "helm_release_pim" {
  triggers = {
    values             = file("./values.yaml")
    tf-helm-pim-values = data.template_file.helm_pim_config.rendered
  }

  depends_on = [
    local_file.helm_pim_config,
    local_file.kubeconfig,
    google_storage_bucket_iam_member.srnt_bucket_pimstorage,
  ]

  provisioner "local-exec" {
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
yq w -i ${path.module}/pim/Chart.yaml version 0.0.0-${var.pim_version}
yq w -i ${path.module}/pim/Chart.yaml appVersion ${var.pim_version}
helm3 repo add akeneo-charts gs://akeneo-charts/
helm3 dependencies update ${path.module}/pim/
export KUBECONFIG="${local_file.kubeconfig.filename}"
helm3 upgrade --atomic --cleanup-on-fail --history-max 5 --create-namespace --wait --install --timeout 20m ${local.pfid} --namespace ${local.pfid} ${path.module}/pim/ -f tf-helm-pim-values.yaml -f values.yaml
EOF
  }
}
