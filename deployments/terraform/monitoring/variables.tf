variable "dns_external" {
  type = string
}

variable "google_project_id" {
  type = string
}

variable "helm_exec_id" {
  type        = string
  description = "Force module dependency based on helm release"
  default     = ""
}

variable "instance_name" {
  type = string
}

variable "pager_duty_service_key" {
  type        = string
  description = "PagerDuty Service key, get it on the pagerduty page of the service"
}

variable "product_reference_code" {
  type = string
}

variable "product_reference_type" {
  type = string
}


