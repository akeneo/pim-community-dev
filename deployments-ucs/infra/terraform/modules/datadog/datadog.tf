data "google_service_account" "timmy_datadog_gcp_integration" {
  project    = var.project_id
  account_id = var.datadog_gcp_integration_id
}

resource "google_service_account_key" "datadog_monitoring" {
  service_account_id = data.google_service_account.timmy_datadog_gcp_integration.name
  public_key_type    = "TYPE_X509_PEM_FILE"
}

resource "datadog_integration_gcp" "gcp_project_integration" {
  project_id     = var.project_id
  private_key_id = jsondecode(base64decode(google_service_account_key.datadog_monitoring.private_key))["private_key_id"]
  private_key    = jsondecode(base64decode(google_service_account_key.datadog_monitoring.private_key))["private_key"]
  client_email   = data.google_service_account.timmy_datadog_gcp_integration.email
  client_id      = data.google_service_account.timmy_datadog_gcp_integration.unique_id
}

module "datadog_pubsub_destination" {
  source                   = "terraform-google-modules/log-export/google//modules/pubsub"
  create_push_subscriber   = true
  create_subscriber        = false
  log_sink_writer_identity = "serviceAccount:${var.datadog_gcp_integration_email}"
  project_id               = var.project_id
  push_endpoint            = "https://gcp-intake.logs.datadoghq.eu/v1/input/${var.datadog_api_key}/"
  topic_name               = "${var.project_id}-timmy-datadog-sink"
}

resource "google_logging_project_sink" "timmy_log_export_sink" {
  name                   = "timmy-app-datadog-log-sink"
  destination            = module.datadog_pubsub_destination.destination_uri
  project                = var.project_id
  filter                 = "resource.type=cloud_function NOT resource.labels.function_name!~\".timmy.\" "
  unique_writer_identity = true
}

resource "google_project_iam_member" "timmy_sink_publisher" {
  project = var.project_id
  role    = "roles/pubsub.publisher"
  member  = google_logging_project_sink.timmy_log_export_sink.writer_identity
}

resource "datadog_logs_custom_pipeline" "timmy_app_cloud_function" {
  filter {
    query = "project_id:${var.project_id} source:gcp.cloud.function"
  }
  name       = "${var.project_id} Timmy-app Cloud Functionn logs processor"
  is_enabled = true
  processor {
    status_remapper {
      sources    = ["data.severity", "data.jsonPayload.level"]
      name       = "Retrieve status from Timmy Cloud Function logs"
      is_enabled = true
    }
  }
  processor {
    date_remapper {
      sources    = ["data.timestamp"]
      name       = "Retrieve timestamp from cloud Function logs"
      is_enabled = true
    }
  }
  processor {
    message_remapper {
      sources    = ["msg", "data.jsonPayload.message"]
      name       = "JSON Payload as log official message"
      is_enabled = true
    }
  }
}
