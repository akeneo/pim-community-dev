locals {
  enable                 = var.enable ? 1 : 0
}

resource "google_cloudfunctions2_function" "this" {
  count       = local.enable
  name        = var.name
  location    = var.location
  description = var.description
  project     = var.project_id
  labels      = var.labels

  build_config {
    runtime     = var.runtime
    entry_point = var.entry_point
    source {
      storage_source {
        bucket = var.bucket_name
        object = google_storage_bucket_object.this[count.index].name
      }
    }
  }

  service_config {
    max_instance_count             = var.max_instance_count
    available_memory               = var.available_memory
    timeout_seconds                = var.timeout_seconds
    environment_variables          = var.environment_variables
    ingress_settings               = var.ingress_settings
    all_traffic_on_latest_revision = var.all_traffic_on_latest_revision
    service_account_email          = var.service_account_email
    vpc_connector                  = var.vpc_connector
    vpc_connector_egress_settings  = var.vpc_connector_egress_settings

    dynamic "secret_environment_variables" {
      for_each = var.secret_environment_variables
      content {
        key        = secret_environment_variables.value["key"]
        project_id = secret_environment_variables.value["project_id"]
        secret     = secret_environment_variables.value["secret"]
        version    = secret_environment_variables.value["version"]
      }
    }
  }
}

data "archive_file" "this" {
  count       = local.enable
  type        = "zip"
  output_path = "/tmp/${var.name}.zip"
  source_dir  = var.source_dir
  excludes    = var.source_dir_excludes
}

resource "google_storage_bucket_object" "this" {
  count  = local.enable
  name   =  "${var.name}.${data.archive_file.this[count.index].output_sha}.zip"
  bucket = var.bucket_name
  source = data.archive_file.this[count.index].output_path
}
