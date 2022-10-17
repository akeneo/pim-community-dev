variable "project_id" {
  description = "The GCP project ID for the cloud function"
  type        = string
}

variable "name" {
  description = "A user-defined name of the function. Function names must be unique globally and match pattern projects/*/locations/*/functions/*"
  type        = string
}

variable "location" {
  description = "The location of this cloud function"
  type        = string
}

variable "description" {
  description = " User-provided description of a function"
  type        = string
  default     = null
}

variable "runtime" {
  description = "The runtime in which to run the function"
  type        = string
  default     = "nodejs16"
}

variable "entry_point" {
  description = "The name of the function (as defined in source code) that will be executed"
  type        = string
}

variable "bucket_name" {
  description = "The bucket name used to store the Cloudfunction zip archive"
  type        = string
}

variable "max_instance_count" {
  description = "The limit on the maximum number of function instances that may coexist at a given time"
  type        = number
  default     = 1
}

variable "available_memory" {
  description = "The amount of memory available for a function. Defaults to 128M. Supported units are k, M, G, Mi, Gi. If no unit is supplied the value is interpreted as bytes"
  type        = string
  default     = "128Mi"
}

variable "timeout_seconds" {
  description = "The function execution timeout. Execution is considered failed and can be terminated if the function is not completed at the end of the timeout period. Defaults to 60 seconds"
  type        = number
  default     = 60
}

variable "environment_variables" {
  description = "User-provided build-time environment variables for the function"
  type        = map(string)
  default     = {}
}

variable "labels" {
  description = "A set of key/value label pairs associated with this Cloud Function"
  type        = map(string)
  default     = {}
}

variable "ingress_settings" {
  description = "Available ingress settings. Defaults to \"ALLOW_ALL\" if unspecified"
  default     = "ALLOW_ALL"
}

variable "all_traffic_on_latest_revision" {
  description = "Whether 100% of traffic is routed to the latest revision. Defaults to true"
  type        = bool
  default     = true
}

variable "service_account_email" {
  description = "The email of the service account for this function"
  type        = string
  default     = null
}

variable "source_dir" {
  description = "The path where the cloud function source code is"
  type        = string
}

variable "source_dir_excludes" {
  description = "Files to exclude from the cloud function archive"
  type        = list(string)
  default     = []
}

variable "secret_environment_variables" {
  description = "Secret environment variables configuration"
  type        = list(object({
    key        = string
    project_id = string
    secret     = string
    version    = string
  }))
  default = []
}

variable "vpc_connector" {
  description = "The Serverless VPC Access connector that this cloud function can connect to"
  type        = string
  default     = null
}

variable "vpc_connector_egress_settings" {
  description = "Available egress settings. Possible values are `VPC_CONNECTOR_EGRESS_SETTINGS_UNSPECIFIED`, `PRIVATE_RANGES_ONLY`, and `ALL_TRAFFIC`"
  type        = string
  default     = null
}
