variable "enable" {
  description = "Deploy or not the module"
  type        = bool
  default     = true
}

variable "project_id" {
  description = "The GCP project id for the cloudscheduler job"
  type        = string
}

variable "region" {
  description = "The region for the cloudscheduler job"
  type        = string
}

variable "name" {
  description = "The name of the job"
  type        = string
}

variable "description" {
  description = "A human-readable description for the job. This string must not contain more than 500 characters"
  type        = string
}

variable "schedule" {
  description = "Describes the schedule on which the job will be executed"
  type        = string
}

variable "oidc_service_account_email" {
  description = "Service account email to be used for generating OAuth token. The service account must be within the same project as the job"
  type        = string
}

variable "oidc_token_audience" {
  description = "Audience to be used when generating OIDC token"
  type        = string
}

variable "time_zone" {
  description = "Specifies the time zone to be used in interpreting schedule. The value of this field must be a time zone name from the tz database"
  type        = string
}

variable "attempt_deadline" {
  description = "The deadline for job attempts. If the request handler does not respond by this deadline then the request is cancelled and the attempt is marked as a DEADLINE_EXCEEDED failure"
  type        = string
}

variable "retry_count" {
  description = "The number of attempts that the system will make to run a job using the exponential backoff procedure described by maxDoublings"
  type        = number
  default     = 0
}

variable "http_method" {
  description = "Which HTTP method to use for the request"
  type        = string
}

variable "http_target_headers" {
  description = "Headers for the http target of the cloud scheduler"
  type        = map(string)
  default     = {
    Content-Type = "application/json"
    User-Agent   = "Google-Cloud-Scheduler"
  }
}

variable "http_target_uri" {
  description = "The full URI path that the request will be sent to"
  type        = string
}

variable "http_target_body" {
  description = "HTTP request body. A request body is allowed only if the HTTP method is POST, PUT, or PATCH. It is an error to set body on a job with an incompatible HttpMethod. A base64-encoded string"
  type        = string
  default     = "{}"
}
