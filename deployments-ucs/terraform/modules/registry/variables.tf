variable "project_id" {
  type        = string
  description = "Project to deploy the registry"
}

variable "registry_region" {
  type        = string
  description = "Target region for the registry"
  default     = "europe-west1"
}

variable "admin_members" {
  type        = list(string)
  description = "Users/Groups/SA allowed to push to the registry"
}

variable "viewer_members" {
  type        = list(string)
  description = "Users/Groups/SA allowed to read from the registry"
}