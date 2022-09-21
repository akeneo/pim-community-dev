variable "project_id" {
  type        = string
  description = "Project to deploy the bucket"
}

variable "bucket_name" {
  type        = string
  description = "Name for the bucket"
}

variable "bucket_region" {
  type        = string
  description = "Target region for the bucket"
  default     = "EU"
}

variable "admin_members" {
  type        = list(string)
  description = "Users/Groups/SA allowed to push to the buckets"
}

variable "viewer_members" {
  type        = list(string)
  description = "Users/Groups/SA allowed to read from the buckets"
}
