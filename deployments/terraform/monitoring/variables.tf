variable "google_project_id" {
  type = string
}

variable "pager_duty_service_key" {
  type        = string
  description = "PagerDuty Service key, get it on the pagerduty page of the service"
}

variable "dns_external" {
  type = string
}

variable "instance_name" {
  type = string
}

