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

variable "types" {
  type    = map
  default = {
    "serenity_instance" = "srnt"
    "growth_edition_instance" = "grth"
    "pim_trial_instance" = "tria"
    }
}

variable "monitoring_authentication_token" {
  type = string
}

variable "monitoring_url" {
  type = string
  default = "/monitoring/services_status"
}

variable "use_edition_flag" {
  type        = bool
  default     = false
  description = "If set to true , it will hardcode srnt-.. in pfid"
}
