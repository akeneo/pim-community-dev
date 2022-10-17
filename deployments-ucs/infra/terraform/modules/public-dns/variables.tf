variable "project_id" {
  type        = string
  description = "Google project"
}

variable "zone_name" {
  type        = string
  description = "Name of the DNS zone"
}

variable "forward" {
  description = "Add zone forward entries to the target zone"
  type = object({
    target_project_id = string
    target_zone_name  = string
  })
  default = null
}