locals {
  metrics = [
    "login-count",
    "login-response-time-distribution",
    "logs-count",
  ]
}

data "template_file" "metric-template" {
  count    = "${length(local.metrics)}"
  template = "${file("${path.module}/templates/metric-${local.metrics[count.index]}.json")}"

  vars {
    pfid = "${var.pfid}"
  }
}

resource "local_file" "metric-rendered" {
  count    = "${length(local.metrics)}"
  content  = "${data.template_file.metric-template.*.rendered[count.index]}"
  filename = "metrics/metric-${local.metrics[count.index]}.json"
}

resource "google_monitoring_uptime_check_config" "https" {
  display_name = "${replace(var.dns_external, "/\\.$/", "")}"
  timeout      = "30s"
  period       = "60s"
  project      = "${var.google_project_name}"

  http_check = {
    path    = "/user/login"
    port    = "443"
    use_ssl = true
  }

  monitored_resource {
    type = "uptime_url"

    labels = {
      project_id = "${var.google_project_name}"
      host       = "${replace(var.dns_external, "/\\.$/", "")}"
    }
  }
}

resource "google_monitoring_notification_channel" "pagerduty" {
  project      = "${var.google_project_name}"
  display_name = "Notification channel of ${var.pfid}"
  type         = "pagerduty"

  labels = {
    service_key = "${var.pager_duty_service_key}"
  }
}

resource "google_monitoring_alert_policy" "alert_policy" {
  display_name = "${var.pfid}"
  combiner = "OR"
  project = "${var.google_project_name}"
  depends_on = ["null_resource.metric"]
  conditions = [
  # Basically it should ring if the volume utilization is > 90% during 15min
  {
    display_name = "Volume utilization for mysql of ${var.pfid}"
    condition_threshold {
      filter = "metric.type=\"kubernetes.io/pod/volume/utilization\" AND resource.type=\"k8s_pod\" AND resource.label.namespace_name=\"${var.pfid}\" AND resource.label.pod_name=monitoring.regex.full_match(\"^${var.pfid}-mysql-server[a-z0-9-]*$\") AND metric.label.volume_name=\"pvc-data\""
      duration = "900s"
      comparison = "COMPARISON_GT"
      threshold_value = 0.9
      aggregations {
        alignment_period = "60s"
        per_series_aligner = "ALIGN_MEAN"
      }
    }
  },
  {
    display_name = "Volume utilization for elasticsearch of ${var.pfid}"
    condition_threshold {
      filter = "metric.type=\"kubernetes.io/pod/volume/utilization\" AND resource.type=\"k8s_pod\" AND resource.label.namespace_name=\"${var.pfid}\" AND resource.label.pod_name=monitoring.regex.full_match(\"^elasticsearch-[a-z0-9-]*$\") AND metric.label.volume_name=\"data\""
      duration = "900s"
      comparison = "COMPARISON_GT"
      threshold_value = 0.9
      aggregations {
        alignment_period = "60s"
        per_series_aligner = "ALIGN_MEAN"
      }
    }
  },
  {
    display_name = "Volume utilization for NFS of ${var.pfid}"
    condition_threshold {
      filter = "metric.type=\"kubernetes.io/pod/volume/utilization\" AND resource.type=\"k8s_pod\" AND resource.label.namespace_name=\"${var.pfid}\" AND resource.label.pod_name=monitoring.regex.full_match(\"^${var.pfid}-nfs-[a-z0-9-]*$\") AND metric.label.volume_name=\"data\""
      duration = "900s"
      comparison = "COMPARISON_GT"
      threshold_value = 0.9
      aggregations {
        alignment_period = "60s"
        per_series_aligner = "ALIGN_MEAN"
      }
    }
  },
  # Basically it should ring if the uptime don't respond or the response time is > 4sec for 15 minutes
  {
    display_name = "Response time of successful requests on /user/login of ${var.pfid}"
    condition_threshold {
      filter          = "metric.type=\"logging.googleapis.com/user/${var.pfid}-login-response-time-distribution\" AND resource.type=k8s_container AND metric.label.response_code=200"
      duration        = "900s"
      comparison      = "COMPARISON_GT"
      threshold_value = 4000000
      aggregations {
        alignment_period     = "60s"
        per_series_aligner   = "ALIGN_SUM"
        cross_series_reducer = "REDUCE_PERCENTILE_95"
      }
    }
  },
  {
    display_name = "Uptime Health Check on /user/login of ${var.pfid}"
    condition_threshold {
      filter          = "metric.type=\"monitoring.googleapis.com/uptime_check/check_passed\" AND resource.type=uptime_url AND resource.label.host=\"${replace(var.dns_external, "/\\.$/", "")}\""
      duration        = "900s"
      comparison      = "COMPARISON_GT"
      threshold_value = 0
      aggregations {
        alignment_period     = "60s"
        per_series_aligner   = "ALIGN_NEXT_OLDER"
        cross_series_reducer = "REDUCE_COUNT_FALSE"
      }
    }
  },
  ]
  notification_channels = [
    "${google_monitoring_notification_channel.pagerduty.name}",
  ]
}

resource "null_resource" "metric" {
  count = "${length(local.metrics)}"

  provisioner "local-exec" {
    command = <<EOF
      gcloud beta logging metrics create ${var.pfid}-${local.metrics[count.index]} \
        --config-from-file ${local_file.metric-rendered.*.filename[count.index]} \
        --project ${var.google_project_name} \
        --quiet && \
      for limit in {1..600}; do \
        [[ ! -z "$(gcloud beta logging metrics list --project ${var.google_project_name} --quiet --format='value(name)' --filter='name~^${var.pfid}-${local.metrics[count.index]}$')" ]] \
        && break || echo "wait $limit"; sleep 1; \
      done;
EOF
  }

  provisioner "local-exec" {
    when = "destroy"

    command = <<EOF
      gcloud logging metrics delete ${var.pfid}-${local.metrics[count.index]} \
        --project ${var.google_project_name} \
        --quiet
EOF
  }
}
