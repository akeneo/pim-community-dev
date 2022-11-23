variable "project_id" {
  type        = string
  description = "Default Project ID"
}

variable "datadog_api_key" {
  type        = string
  description = "API key for datadog"
}

variable "datadog_app_key" {
  type        = string
  description = "APP key for datadog"
}

variable "datadog_gcp_integration_email" {
  type        = string
  description = "Service account email for the datadog/GCP integration"
}

variable "datadog_gcp_integration_id" {
  type        = string
  description = "Service account ID for the datadog/GCP integration"
}
