variable "project_id" {
  type        = string
  description = "Google project"
}

variable "zone_name" {
  type        = string
  description = "Name of the DNS zone"
}

variable "networks" {
  type        = list(string)
  description = "List of networks that will have access to the zone"
}
