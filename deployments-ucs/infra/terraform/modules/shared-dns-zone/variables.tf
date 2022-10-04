variable "project_id" {
  type        = string
  description = "Google project"
}

variable "zone_name" {
  type        = string
  description = "Name of the DNS zone"
}

variable "admins" {
  description = "List of DNS records admins"
  type        = list(string)
}
