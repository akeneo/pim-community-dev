variable "project_id" {
  description = "Project wich will hosts the service accounts"
  type        = string
}

variable "account_id" {
  description = "Name of the service account"
  type        = string
}

variable "service_account_description" {
  description = "Description of the service account"
  type        = string
  default     = ""
}

variable "admins" {
  description = "Cloudbuild admins"
  type        = list(string)
  default     = []
}
