terraform {
  required_providers {
    datadog = {
      source = "datadog/datadog"
    }
  }
}

resource "datadog_integration_gcp" "gcp_project_integration" {
  project_id     = var.project_id
  private_key_id = jsondecode(base64decode(google_service_account_key.datadog_monitoring.private_key))["private_key_id"]
  private_key    = jsondecode(base64decode(google_service_account_key.datadog_monitoring.private_key))["private_key"]
  client_email   = google_service_account.timmy_datadog_gcp_integration.email
  client_id      = google_service_account.timmy_datadog_gcp_integration.unique_id
}

resource "google_service_account" "timmy_datadog_gcp_integration" {
  account_id   = "timmy-app-datadog-sa"
  project      = var.project_id
  display_name = "Timmy Datadog Google Cloud integration service account"
}

resource "google_service_account_key" "datadog_monitoring" {
  service_account_id = google_service_account.timmy_datadog_gcp_integration.name
  public_key_type    = "TYPE_X509_PEM_FILE"
}

resource "google_logging_project_sink" "timmy-log-export-sink" {
  name                   = "${var.region}-timmy-app-datadog-log-sink"
  destination            = module.datadog_pubsub_destination.destination_uri
  project                = var.project_id
  filter                 = "resource.type=cloud_function NOT resource.labels.function_name!~\".timmy.\" "
  unique_writer_identity = true
}

module "datadog_pubsub_destination" {
  source                   = "terraform-google-modules/log-export/google//modules/pubsub"
  create_push_subscriber   = true
  create_subscriber        = false
  log_sink_writer_identity = "serviceAccount:${google_service_account.timmy_datadog_gcp_integration.email}"
  project_id               = var.project_id
  push_endpoint            = "https://gcp-intake.logs.datadoghq.eu/v1/input/${var.datadog_api_key}/"
  topic_name               = "${var.region}-timmy-datadog-sink"
}

resource "datadog_logs_custom_pipeline" "timmy_app_cloud_function" {
  filter {
    query = "project_id:${var.project_id} source:gcp.cloud.function"
  }
  name       = "${var.project_id}-${var.region} Timmy-app Cloud Functionn logs processor"
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

resource "google_project_iam_member" "datadog_compute_viewer" {
  project = var.project_id
  role    = "roles/compute.viewer"
  member  = "serviceAccount:${google_service_account.timmy_datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "datadog_monitoring_viewer" {
  project = var.project_id
  role    = "roles/monitoring.viewer"
  member  = "serviceAccount:${google_service_account.timmy_datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "datadog_cloudasset_viewer" {
  project = var.project_id
  role    = "roles/cloudasset.viewer"
  member  = "serviceAccount:${google_service_account.timmy_datadog_gcp_integration.email}"
}

resource "google_project_iam_member" "timmy_sink_publisher" {
  project = var.project_id
  role    = "roles/pubsub.publisher"
  member  = google_logging_project_sink.timmy-log-export-sink.writer_identity
}
