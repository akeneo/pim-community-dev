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
    papoProjectCodeHashed           = md5(var.papo_project_code)
    pimVersion                      = var.pim_version
    monitoring_authentication_token = random_string.monitoring_authentication_token.result
    mysql_disk_name                 = google_compute_disk.mysql-disk.name
    mysql_disk_size                 = google_compute_disk.mysql-disk.size
    mysql_disk_storage_class        = google_compute_disk.mysql-disk.type == "pd-ssd" ? "ssd-retain" : "standard-retain"
    subscription_webhook            = google_pubsub_subscription.webhook.name
    subscription_job_queue          = google_pubsub_subscription.job-queue.name
    topic_business_event            = google_pubsub_topic.business-event.name
    topic_job_queue                 = google_pubsub_topic.job-queue.name
  }
}

resource "local_file" "helm_pim_config" {
  content  = data.template_file.helm_pim_config.rendered
  filename = "./tf-helm-pim-values.yaml"
}

data "helm_repository" "stable" {
  name = "stable"
  url  = "https://charts.helm.sh/stable"
}

data "helm_repository" "akeneo-charts" {
  name = "akeneo-charts"
  url  = "gs://akeneo-charts"
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
helm dependencies update ${path.module}/pim/
yq w -i ${path.module}/pim/Chart.yaml version ${var.pim_version}
yq w -i ${path.module}/pim/Chart.yaml appVersion ${var.pim_version}
export KUBECONFIG="${local_file.kubeconfig.filename}"
kubectl delete -n ${local.pfid} cronjob --all
kubectl scale -n ${local.pfid} deploy/pim-web deploy/pim-daemon-default --replicas=0
kubectl scale -n ${local.pfid} deploy/pim-daemon-webhook-consumer-process --replicas=0 || true
kubectl scale -n ${local.pfid} deploy/pim-daemon-all-but-linking-assets-to-products --replicas=0 || true
helm upgrade --atomic --cleanup-on-fail --wait --install --force --timeout 1202 ${local.pfid} --namespace ${local.pfid} ${path.module}/pim/ -f tf-helm-pim-values.yaml -f values.yaml
HELM_STATUS_CODE=$${?}
kubectl scale -n ${local.pfid} deploy/pim-web --replicas=2
kubectl scale -n ${local.pfid} deploy/pim-daemon-default deploy/pim-daemon-all-but-linking-assets-to-products deploy/pim-daemon-webhook-consumer-process --replicas=1
KUBECTL_SCALE_CODE=$${?}
if [ $${KUBECTL_SCALE_CODE} -eq 0 ]; then
  exit $${HELM_STATUS_CODE}
else
  exit $${KUBECTL_SCALE_CODE}
fi
EOF
  }
}
